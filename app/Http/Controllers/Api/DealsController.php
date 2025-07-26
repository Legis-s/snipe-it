<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\DealsTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Deal;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Location;
use App\Http\Transformers\SelectlistTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Auth;
use Illuminate\Support\Facades\DB;

class DealsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|array
    {
        $this->authorize('view', Location::class);
        $allowed_columns = [
            'id', 'name', 'address', 'created_at', 'updated_at', 'manager_id',
            'assigned_assets_count', 'users_count', 'assets_count', 'currency'
        ];

        $deals = Deal::select([
            'deals.id',
            'deals.name',
            'deals.number',
            'deals.status',
            'deals.type',
            'deals.date_start',
            'deals.date_end',
            'deals.bitrix_id',
            'deals.summ',
            'deals.created_at',
            'deals.updated_at',
        ]);

        if ($request->filled('search')) {
            $deals = $deals->TextSearch($request->input('search'));
        }
        if ($request->filled('sum_error') && $request->input('sum_error') == 1) {
            $deals = $deals->havingRaw('assets_sum + consumables_cost > contracts.summ');
        }

        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($deals) && ($request->get('offset') > $deals->count())) ? $deals->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        switch ($request->input('sort')) {
            case 'parent':
                $deals->OrderParent($order);
                break;
            case 'manager':
                $deals->OrderManager($order);
                break;
            default:
                $deals->orderBy($sort, $order);
                break;
        }


        $total = $deals->count();
        $deals = $deals->skip($offset)->take($limit)->get();
        return (new DealsTransformer)->transformDeals($deals, $total);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @since [v4.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function show($id): JsonResponse | array
    {
        $this->authorize('view', Deal::class);
        $deal = Deal::with('')
            ->select([
                'deals.id',
                'deals.name',
                'deals.number',
                'deals.status',
                'deals.date_start',
                'deals.date_end',
                'deals.bitrix_id',
                'deals.created_at',
                'deals.updated_at',
            ])
            ->findOrFail($id);
        return (new DealsTransformer())->transformDeal($deal);
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

        $deals = Deal::select([
            'deals.id',
            'deals.name',
            'deals.number',
            'deals.status',
        ]);

        $page = 1;
        if ($request->filled('page')) {
            $page = $request->input('page');
        }

        if ($request->filled('search')) {
            $deals = $deals->where('deals.name', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('deals.number', 'LIKE', '%' . $request->input('search') . '%');
        }

        $deals = $deals->orderBy('name', 'ASC')->get();

        foreach ($deals as $deal) {
            $name_str = '';
            if ($deal->number != '') {
                $name_str .= "[" . e($deal->number) . '] ';
            }
            $name_str .= e($deal->name);

//            preg_replace('/&quot;/', '"', $name_str);
            $deal->use_text = preg_replace('/&quot;/', '"', $name_str);;
        }

        $paginated_results = new LengthAwarePaginator($deals->forPage($page, 500), $deals->count(), 500, $page, []);

        return (new SelectlistTransformer)->transformSelectlist($paginated_results);

    }
}