<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\ContractsTransformer;
use App\Models\Contract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Location;
use App\Http\Transformers\LocationsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
            'contracts.date_start',
            'contracts.date_end',
            'contracts.bitrix_id',
            'contracts.created_at',
            'contracts.updated_at',
        ]);

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


}