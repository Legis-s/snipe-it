<?php


namespace App\Http\Controllers\Api;


use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\ComponentsTransformer;
use App\Http\Transformers\ConsumableAssignmentTransformer;
use App\Http\Transformers\LocationsTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Contract;
use App\Models\User;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Location;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsumableAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Consumable::class);
        $allowed_columns = [
            'id','cost','assigned_to','consumable_id','user_id','assigned_type','comment','type',
            'quantity','created_at','updated_at'];

        $consumableAssignments = ConsumableAssignment::with('user','assignedTo','consumable','contract')->select([
            'consumables_locations.id',
            'consumables_locations.cost',
            'consumables_locations.consumable_id',
            'consumables_locations.user_id',
            'consumables_locations.assigned_type',
            'consumables_locations.assigned_to',
            'consumables_locations.contract_id',
            'consumables_locations.comment',
            'consumables_locations.type',
            'consumables_locations.quantity',
            'consumables_locations.created_at',
            'consumables_locations.updated_at',
        ]);


        if ($request->filled('search')) {
            $consumableAssignments = $consumableAssignments->AssignedSearch($request->input('search'));
        }

        if ($request->filled('no_contract') ) {
            $consumableAssignments->where('type','=','sold');
            $consumableAssignments->where('assigned_type',"App\Models\User");
        }


        if ($request->filled('consumable_id')) {
            $consumableAssignments->where('consumable_id','=',$request->input('consumable_id'));
        }
        if ($request->filled('asset_id')) {
            $consumableAssignments->where('assigned_to', $request->input('asset_id'));
            $consumableAssignments->where('assigned_type',"App\Models\Asset");
        }
        if ($request->filled('user_id')) {
            $consumableAssignments->where('assigned_to', $request->input('user_id'));
            $consumableAssignments->where('assigned_type',"App\Models\User");
        }

        if ($request->filled('location_id')) {
            $consumableAssignments->where('assigned_to', $request->input('location_id'));
            $consumableAssignments->where('assigned_type',"App\Models\Location");
        }
        if ($request->filled('contract_id')) {
            $consumableAssignments->where('assigned_to', $request->input('contract_id'));
            $consumableAssignments->where('assigned_type',"App\Models\Contract");
            $consumableAssignments->orWhere('contract_id', $request->input('contract_id'));
        }

        if ($request->filled('deal_id')) {
            $consumableAssignments->where('assigned_to', $request->input('deal_id'));
            $consumableAssignments->where('assigned_type',"App\Models\Deal");
//            $consumableAssignments->orWhere('contract_id', $request->input('contract_id'));
        }

        if ($request->filled('purchase_id')) {
            $consumableAssignments->where('assigned_to', $request->input('purchase_id'));
            $consumableAssignments->where('assigned_type',"App\Models\Purchase");
        }


        if ($request->filled('massoperation_id')) {
            $consumableAssignments->join('cons_assignment_mass_operation', 'cons_assignment_mass_operation.consumable_assignment_id', '=', 'consumables_locations.id')->where('cons_assignment_mass_operation.mass_operation_id', '=', $request->input('massoperation_id'));
        }


        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($consumableAssignments) && ($request->get('offset') > $consumableAssignments->count())) ? $consumableAssignments->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        $consumableAssignments->orderBy('consumables_locations.created_at', 'desc')->orderBy('consumables_locations.id', 'desc');
        $total = $consumableAssignments->count();
        $consumableAssignments = $consumableAssignments->skip($offset)->take($limit)->get();
        return (new ConsumableAssignmentTransformer)->transformConsumableAssignments($consumableAssignments, $total);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     * @since [v4.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function return(Request $request, $id)
    {
        $this->authorize('view', Consumable::class);
        $consumableAssignment = ConsumableAssignment::findOrFail($id);
        $user = Auth::user();


        if ($request->filled('quantity')) {
            $consumableAssignment->quantity = $consumableAssignment->quantity - $request->input('quantity');
            $user_name = "(".$user->id.") ".$user->last_name." ".$user->first_name;
            $consumableAssignment->comment = $consumableAssignment->comment." Возвращено: ".$request->input('quantity').", ".date("Y-m-d H:i:s").", ".$user_name;
            if ($consumableAssignment->save()) {

                $log = new Actionlog();
                $log->user_id = Auth::id();
                $log->action_type = 'return';
//                $log->target_type = "App\Models\Contract";
//                $log->target_id = $contract_id;
                $log->item_id = $consumableAssignment->consumable_id;
                $log->item_type = Consumable::class;
                $log->note = json_encode($request->all());
                $log->save();

                return response()->json(Helper::formatStandardApiResponse('success', $consumableAssignment, trans('admin/consumables/message.update.success')));
            }
        } else {
            return response()->json(Helper::formatStandardApiResponse('error', null, $consumableAssignment->getErrors()));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $consumableAssignment->getErrors()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     * @since [v4.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function close_documents(Request $request, $id)
    {
        $this->authorize('view', Consumable::class);

        $consumableAssignment = ConsumableAssignment::findOrFail($id);

        $user_pre = User::findOrFail($consumableAssignment->assigned_to);
        $user = Auth::user();


        $contract = $consumableAssignment->contract;
        if (!$contract && $request->filled('contract_id')){
            $contract_id = $request->input('contract_id');
            $contract = Contract::findOrFail($contract_id);
        }
        $consumableAssignment->assigned_type = Contract::class;
        $consumableAssignment->assigned_to = $contract->id;


        $user_name = "(".$user->id.") ".$user->last_name." ".$user->first_name;
        $user_name_pre = "(".$user_pre->id.") ".$user_pre->last_name." ".$user_pre->first_name;
        $consumableAssignment->comment = $consumableAssignment->comment." Закрывающие документы получены ".date("Y-m-d H:i:s").", ".$user_name." Расходник списан с пользователя: ".$user_name_pre;

        if ($consumableAssignment->save()) {
            $log = new Actionlog();
            $log->user_id = Auth::id();
            $log->action_type = 'sell';
            $log->target_type = Contract::class;
            $log->target_id = $contract->id;
            $log->item_id = $consumableAssignment->consumable_id;
            $log->item_type = Consumable::class;
            $log->note = json_encode($request->all());
            $log->save();

            return response()->json(Helper::formatStandardApiResponse('success', $consumableAssignment, trans('admin/consumables/message.update.success')));
        }else {
            return response()->json(Helper::formatStandardApiResponse('error', null, $consumableAssignment->getErrors()));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $consumableAssignment->getErrors()));
    }
}