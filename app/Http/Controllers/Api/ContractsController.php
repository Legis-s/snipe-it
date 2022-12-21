<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\ContractsTransformer;
use App\Models\Asset;
use App\Models\Contract;
use App\Models\Statuslabel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Location;
use App\Http\Transformers\LocationsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Auth;

class ContractsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Location::class);
        $allowed_columns = [
            'id','name','address','address2','city','state','country','zip','created_at',
            'updated_at','manager_id','image',
            'assigned_assets_count','users_count','assets_count','currency'];

        $contracts = Contract::select([
            'contracts.id',
            'contracts.name',
            'contracts.number',
            'contracts.status',
            'contracts.type',
            'contracts.date_start',
            'contracts.date_end',
            'contracts.bitrix_id',
            'contracts.summ',
            'contracts.created_at',
            'contracts.updated_at',
        ])->withSum('assets', 'purchase_cost')
            ->withCount('assets as assets_count')
            ->withCount('assets_no_docs as assets_no_docs_count');
        \Debugbar::info($contracts);

        if ($request->filled('search')) {
            $contracts = $contracts->TextSearch($request->input('search'));
        }

        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($contracts) && ($request->get('offset') > $contracts->count())) ? $contracts->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        switch ($request->input('sort')) {
            case 'parent':
                $contracts->OrderParent($order);
                break;
            case 'manager':
                $contracts->OrderManager($order);
                break;
            default:
                $contracts->orderBy($sort, $order);
                break;
        }


        $total = $contracts->count();
        $contracts = $contracts->skip($offset)->take($limit)->get();
        return (new ContractsTransformer)->transformContracts($contracts, $total);
    }


    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', Contract::class);
        $contract = Contract::with('')
            ->select([
                'contracts.id',
                'contracts.name',
                'contracts.number',
                'contracts.status',
                'contracts.date_start',
                'contracts.date_end',
                'contracts.bitrix_id',
                'contracts.created_at',
                'contracts.updated_at',
            ])
        ->findOrFail($id);
        return (new ContractsTransformer)->transformContracts($contract);
    }



    /**
     * Gets a paginated collection for the select2 menus
     *
     * This is handled slightly differently as of ~4.7.8-pre, as
     * we have to do some recursive magic to get the hierarchy to display
     * properly when looking at the parent/child relationship in the
     * rich menus.
     *
     * This means we can't use the normal pagination that we use elsewhere
     * in our selectlists, since we have to get the full set before we can
     * determine which location is parent/child/grandchild, etc.
     *
     * This also means that hierarchy display gets a little funky when people
     * use the Select2 search functionality, but there's not much we can do about
     * that right now.
     *
     * As a result, instead of paginating as part of the query, we have to grab
     * the entire data set, and then invoke a paginator manually and pass that
     * through to the SelectListTransformer.
     *
     * Many thanks to @uberbrady for the help getting this working better.
     * Recursion still sucks, but I guess he doesn't have to get in the
     * sea... this time.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     *
     */
    public function selectlist(Request $request)
    {

        $contracts = Contract::select([
            'contracts.id',
            'contracts.name',
            'contracts.number',
        ]);

        $page = 1;
        if ($request->filled('page')) {
            $page = $request->input('page');
        }

        if ($request->filled('search')) {
            $contracts = $contracts->where('contracts.name', 'LIKE', '%'.$request->input('search').'%')
                ->orWhere('contracts.number', 'LIKE', '%'.$request->input('search').'%');
        }

        $contracts = $contracts->orderBy('name', 'ASC')->get();

        foreach ($contracts as $contract) {
            $name_str = '';
            if ($contract->number!='') {
                $name_str .= "[".e($contract->number).'] ';
            }
            $name_str .= e($contract->name);


            $contract->use_text = $name_str;
        }

        $paginated_results =  new LengthAwarePaginator($contracts->forPage($page, 500), $contracts->count(), 500, $page, []);

//        return [];
        return (new SelectlistTransformer)->transformSelectlist($paginated_results);

    }

    /**
     * Returns JSON listing of all requestable assets
     *
     * @return JsonResponse
     * @since [v4.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function closesell(Request $request, $contract_id)
    {
        $this->authorize('checkout', Asset::class);

        $contract = Contract::findOrFail($contract_id);
        $status = Statuslabel::where('name', 'Выдано')->first();
        $assets = Asset::where("contract_id",$contract->id)->where("status_id",$status->id)->whereNotNull("assigned_to")->get();;
        $target = $contract;
        $checkout_at = request('checkout_at', date('Y-m-d H:i:s'));
        foreach ($assets as &$asset) {
            $asset->closeSell($target, Auth::user(), $checkout_at, null, null);
        }
//        \Debugbar::info($assets);
//        $target = Contract::find( $asset->contract_id);
//        $note = request('note', null);
//        $asset_name = request('name', null);
//        $checkout_at = request('checkout_at', date('Y-m-d H:i:s'));
//
//        if (! isset($target)) {
//            return response()->json(Helper::formatStandardApiResponse('error', $error_payload, 'Checkout target for asset '.e($asset->asset_tag).' is invalid - '.$error_payload['target_type'].' does not exist.'));
//        }
//
//        if ($asset->closeSell($target, Auth::user(), $checkout_at, $note, $asset_name)) {
//            return response()->json(Helper::formatStandardApiResponse('success', ['asset'=> e($asset->asset_tag)], trans('admin/hardware/message.checkout.success')));
//        }
        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.checkout.success')));
//
//        return response()->json(Helper::formatStandardApiResponse('error', ['asset'=> e($asset->asset_tag)], trans('admin/hardware/message.checkout.error')));
    }




}