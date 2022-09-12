<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\LicensesTransformer;
use App\Http\Transformers\MassOperationsTransformer;
use App\Http\Transformers\PurchasesTransformer;
use App\Models\MassOperation;
use App\Models\Users;
use App\Models\License;
use App\Models\Purchase;
use App\Models\Statuslabel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MassOperationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', MassOperation::class);
        $massOperations = MassOperation::with('assets')
            ->leftJoin('users', 'mass_operations.assigned_to', '=', 'users.id')
            ->join('locations', 'mass_operations.assigned_to', '=', 'locations.id')
            ->join('contracts', 'mass_operations.assigned_to', '=', 'contracts.id')
            ->select(['mass_operations.id',
                'mass_operations.operation_type',
                'mass_operations.name',
                'mass_operations.user_id',
                'mass_operations.contract_id',
                'mass_operations.assigned_type',
                'mass_operations.assigned_to',
                'mass_operations.bitrix_task_id',
                'mass_operations.note',
                'mass_operations.created_at',
                'users.first_name',
                'users.last_name',
                'contracts.name as contract_name',
                'locations.name as location_name'])
            ->withCount(['assets as assets_count']);
//        \Log::info($massOperations->get());
//

//var_dump($massOperations);
//        if ($request->filled('search')) {
//            $massOperations = $massOperations->TextSearch($request->input('search'));
//
//        }
//        if ($request->filled('user_id')) {
//            $massOperations->where('user_id', '=', $request->input('user_id'));
//        }
//        if ($request->filled('status')) {
//            $massOperations->where('status', '=', $request->input('status'));
//        }
//        if ($request->filled('supplier')) {
//            $purchases->where('supplier_id', '=', $request->input('supplier'));
//        }

        $allowed_columns =
            [
                'id', 'name'
//                'bitrix_id', 'final_price', 'status', 'created_at',
//                'deleted_at'
        ];


        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        if ($request->input('not_finished_status')) {
            $massOperations->where('status', '<>', "finished");
        }

        $massOperations->orderBy($sort, $order);
// Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
// case we override with the actual count, so we should return 0 items.
        $offset = (($massOperations) && ($request->get('offset') > $massOperations->count())) ? $massOperations->count() : $request->get('offset', 0);
//
// Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');


        $total = $massOperations->count();
        $massOperations = $massOperations->skip($offset)->take($limit)->get();
//        dd($massOperations);
        return (new MassOperationsTransformer())->transformMassOperations($massOperations, $total);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
