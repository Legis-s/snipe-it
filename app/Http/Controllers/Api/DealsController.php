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
            'id', 'name', 'number', 'created_at', 'assets_count', 'consumable_count',
            'assets_sum_purchase_cost', 'consumables_sum_purchase_cost', 'summ', 'bitrix_id'
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
        ])->withSum('assignedAssets as assets_sum_purchase_cost', 'purchase_cost')
            ->withCount('assignedAssets as assets_count')
            ->withCount('assignedConsumables as consumable_count')
            ->addSelect(['consumables_sum_purchase_cost' => ConsumableAssignment::query()
                ->whereColumn('assigned_to', 'deals.id')
                ->where('assigned_type', Deal::class)
                ->selectRaw('sum(quantity * cost)')
            ]);


        if ($request->filled('filter') || $request->filled('search')) {
            $deals->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('sum_error') && $request->input('sum_error') == 1) {
            $deals->havingRaw(
                'COALESCE(assets_sum_purchase_cost, 0) + COALESCE(consumables_sum_purchase_cost, 0) > deals.summ'
            );
        }

        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        switch ($request->input('sort')) {
            case 'company':
                $deals->OrderCompany($order);
                break;
            default:
                $deals->orderBy($sort, $order);
                break;
        }

        $total = $deals->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $deals = $deals->skip($offset)->take($limit)->get();

        return (new DealsTransformer)->transformDeals($deals, $total);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     */
    public function show(int $id): JsonResponse | array
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
     * @see \App\Http\Transformers\SelectlistTransformer
     *
     */
    public function selectlist(Request $request): array
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
            $deal->use_text = preg_replace('/&quot;/', '"', $name_str);;
        }

        $paginated_results = new LengthAwarePaginator($deals->forPage($page, 500), $deals->count(), 500, $page, []);

        return (new SelectlistTransformer)->transformSelectlist($paginated_results);
    }
}
