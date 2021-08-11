<?php


namespace App\Http\Controllers\Api;


use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\ComponentsTransformer;
use App\Http\Transformers\ConsumableAssignmentTransformer;
use App\Http\Transformers\LocationsTransformer;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Location;
use App\Models\Purchase;
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

        $consumableAssignments = ConsumableAssignment::with('user','assignedTo')->select([
            'consumables_locations.id',
            'consumables_locations.cost',
            'consumables_locations.consumable_id',
            'consumables_locations.user_id',
            'consumables_locations.assigned_type',
            'consumables_locations.assigned_to',
            'consumables_locations.comment',
            'consumables_locations.type',
            'consumables_locations.quantity',
            'consumables_locations.created_at',
            'consumables_locations.updated_at',
        ]);


        if ($request->filled('search')) {
            $consumableAssignments = $consumableAssignments->TextSearch($request->input('search'));
        }

        if ($request->filled('consumable_id')) {
            $consumableAssignments->where('consumable_id','=',$request->input('consumable_id'));
        }


        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($consumableAssignments) && ($request->get('offset') > $consumableAssignments->count())) ? $consumableAssignments->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        $consumableAssignments->orderBy('consumables_locations.id', 'desc');
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
        if ($request->filled('quantity')) {
            $consumableAssignment->quantity = $consumableAssignment->quantity - $request->filled('quantity');
            $user = Auth::user();
            $user_name = "(".$user->id.") ".$user->last_name." ".$user->first_name;
            $consumableAssignment->comment = $consumableAssignment->comment." Возвращено: ".$request->filled('quantity').", ".date("Y-m-d H:i:s").", ".$user_name;
            if ($consumableAssignment->save()) {
                return response()->json(Helper::formatStandardApiResponse('success', $consumableAssignment, trans('admin/consumables/message.update.success')));
            }
        } else {
            return response()->json(Helper::formatStandardApiResponse('error', null, $consumableAssignment->getErrors()));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $consumableAssignment->getErrors()));
    }
}