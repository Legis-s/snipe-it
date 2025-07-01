<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\LicensesTransformer;
use App\Http\Transformers\MassOperationsTransformer;
use App\Http\Transformers\PurchasesTransformer;
use App\Models\Asset;
use App\Models\Location;
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
        $this->authorize('view', Asset::class);


        $allowed_columns = [
            'id', 'operation_type', 'name', 'created_by', 'contract_id', 'assigned_type', 'assigned_to', 'bitrix_task_id', 'note',
            'created_at',  'assets_count', 'consumables_count' ];


        $massOperations = MassOperation::with('assignedTo','assets','consumables','adminuser')->select([
            'mass_operations.id',
            'mass_operations.operation_type',
            'mass_operations.name',
            'mass_operations.created_by',
            'mass_operations.contract_id',
            'mass_operations.assigned_type',
            'mass_operations.assigned_to',
            'mass_operations.bitrix_task_id',
            'mass_operations.note',
            'mass_operations.created_at',
            'mass_operations.updated_at',
        ])->withCount('assets as assets_count')
            ->withCount('consumables as consumables_count');


        if ($request->filled('search')) {
            $massOperations = $massOperations->TextSearch($request->input('search'));
        }

        $offset = (($massOperations) && (request('offset') > $massOperations->count())) ? $massOperations->count() : request('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
        $massOperations->orderBy($sort, $order);
        $total = $massOperations->count();
        $massOperations = $massOperations->skip($offset)->take($limit)->get();

        return (new MassOperationsTransformer())->transformMassOperations($massOperations, $total);
    }
}
