<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\ConsumablesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Actionlog;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Support\Facades\Auth;

class ConsumablesController extends Controller
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
        $this->authorize('index', Consumable::class);

        // This array is what determines which fields should be allowed to be sorted on ON the table itself, no relations
        // Relations will be handled in query scopes a little further down.
        $allowed_columns = 
            [
                'id',
                'name',
                'order_number',
                'min_amt',
                'purchase_date',
                'purchase_cost',
                'company',
                'category',
                'model_number', 
                'item_no', 
                'qty',
                'image',
                'notes',
                ];


        $consumables = Company::scopeCompanyables(
            Consumable::select('consumables.*')
                ->with('company', 'location', 'category', 'users', 'manufacturer','purchase')
        );

        if ($request->filled('search')) {
            $consumables = $consumables->TextSearch(e($request->input('search')));
        }

        if ($request->filled('name')) {
            $consumables->where('name', '=', $request->input('name'));
        }

        if ($request->filled('company_id')) {
            $consumables->where('company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('category_id')) {
            $consumables->where('category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('model_number')) {
            $consumables->where('model_number','=',$request->input('model_number'));
        }

        if ($request->filled('manufacturer_id')) {
            $consumables->where('manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $consumables->where('supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('location_id')) {
            $consumables->where('location_id','=',$request->input('location_id'));
        }

        if ($request->filled('notes')) {
            $consumables->where('notes','=',$request->input('notes'));
        }

        if ($request->filled('purchase_id')) {
            $consumables->where('purchase_id', '=', $request->input('purchase_id'));
        }


        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $consumables->count()) ? $consumables->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $allowed_columns = ['id', 'name', 'order_number', 'min_amt', 'purchase_date', 'purchase_cost', 'company', 'category', 'model_number', 'item_no', 'manufacturer', 'location', 'qty', 'image'];
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';


        switch ($sort_override) {
            case 'category':
                $consumables = $consumables->OrderCategory($order);
                break;
            case 'location':
                $consumables = $consumables->OrderLocation($order);
                break;
            case 'manufacturer':
                $consumables = $consumables->OrderManufacturer($order);
                break;
            case 'company':
                $consumables = $consumables->OrderCompany($order);
                break;
            case 'supplier':
                $components = $consumables->OrderSupplier($order);
                break;
            default:
                $consumables = $consumables->orderBy($column_sort, $order);
                break;
        }

        $total = $consumables->count();
        $consumables = $consumables->skip($offset)->take($limit)->get();

        return (new ConsumablesTransformer)->transformConsumables($consumables, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ImageUploadRequest $request)
    {
        $this->authorize('create', Consumable::class);
        $consumable = new Consumable;
        $consumable->fill($request->all());
        $consumable = $request->handleImages($consumable);

        if ($consumable->save()) {
            $consumableAssignment = new ConsumableAssignment;
            $consumableAssignment->type = ConsumableAssignment::MANUALLY;
            $consumableAssignment->quantity = $consumable->qty;
            $consumableAssignment->cost = $consumable->purchase_cost;
            $consumableAssignment->user_id = Auth::id();
            $consumableAssignment->consumable_id = $consumable->id;
            $consumableAssignment->save();

            return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', Consumable::class);
        $consumable = Consumable::with('users')->findOrFail($id);

        return (new ConsumablesTransformer)->transformConsumable($consumable);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ImageUploadRequest $request, $id)
    {
        $this->authorize('update', Consumable::class);
        $consumable = Consumable::findOrFail($id);
        $consumable->fill($request->all());
        $consumable = $request->handleImages($consumable);
        
        if ($consumable->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', Consumable::class);
        $consumable = Consumable::findOrFail($id);
        $this->authorize('delete', $consumable);
        $consumable->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.delete.success')));
    }

    /**
    * Returns a JSON response containing details on the users associated with this consumable.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see \App\Http\Controllers\Consumables\ConsumablesController::getView() method that returns the form.
    * @since [v1.0]
    * @param int $consumableId
    * @return array
     */
    public function getDataView($consumableId)
    {
        $consumable = Consumable::with(['consumableAssignments'=> function ($query) {
            $query->orderBy($query->getModel()->getTable().'.created_at', 'DESC');
        },
        'consumableAssignments.admin'=> function ($query) {
        },
        'consumableAssignments.user'=> function ($query) {
        },
        ])->find($consumableId);

        if (! Company::isCurrentUserHasAccess($consumable)) {
            return ['total' => 0, 'rows' => []];
        }
        $this->authorize('view', Consumable::class);
        $rows = [];

        foreach ($consumable->consumableAssignments as $consumable_assignment) {
            $rows[] = [
                'avatar' => ($consumable_assignment->user) ? e($consumable_assignment->user->present()->gravatar) : '',
                'name' => ($consumable_assignment->user) ? $consumable_assignment->user->present()->nameUrl() : 'Deleted User',
                'created_at' => Helper::getFormattedDateObject($consumable_assignment->created_at, 'datetime'),
                'note' => ($consumable_assignment->note) ? e($consumable_assignment->note) : null,
                'admin' => ($consumable_assignment->admin) ? $consumable_assignment->admin->present()->nameUrl() : null,
            ];
        }

        $consumableCount = $consumable->users->count();
        $data = ['total' => $consumableCount, 'rows' => $rows];

        return $data;
    }

    /**
     * Checkout a consumable
     *
     * @author [A. Gutierrez] [<andres@baller.tv>]
     * @param int $id
     * @since [v4.9.5]
     * @return JsonResponse
     */
    public function checkout(Request $request, $id)
    {
        // Check if the consumable exists
        if (!$consumable = Consumable::with('users')->find($id)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.does_not_exist')));
        }

        $this->authorize('checkout', $consumable);

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.checkout.unavailable')));
        }

        // Make sure there is a valid category
        if (!$consumable->category){
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.invalid_item_category_single', ['type' => trans('general.consumable')])));
        }


        // Check if the user exists - @TODO:  this should probably be handled via validation, not here??
        if (!$user = User::find($request->input('assigned_to'))) {
            // Return error message
            return response()->json(Helper::formatStandardApiResponse('error', null, 'No user found'));
            \Log::debug('No valid user');
        }

        // Update the consumable data
        $consumable->assigned_to = $request->input('assigned_to');

        $consumable->users()->attach($consumable->id,
                [
                    'consumable_id' => $consumable->id,
                    'user_id' => $user->id,
                    'assigned_to' => $request->input('assigned_to'),
                    'note' => $request->input('note'),
                ]
            );

            // Log checkout event
            $logaction = $consumable->logCheckout($request->input('note'), $user);
            $data['log_id'] = $logaction->id;
            $data['eula'] = $consumable->getEula();
            $data['first_name'] = $user->first_name;
            $data['item_name'] = $consumable->name;
            $data['checkout_date'] = $logaction->created_at;
            $data['note'] = $logaction->note;
            $data['require_acceptance'] = $consumable->requireAcceptance();

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.checkout.success')));

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
    public function review(Request $request, $id)
    {
        $this->authorize('review');
        $consumable = Consumable::withTrashed()->findOrFail($id);
        if ($consumable->trashed()) {
            $consumable->qty = 0;
            $consumable->save();
            $consumable->restore();
        }
        if ($request->filled('purchase_id') && $request->filled('quantity')) {

            $purchase_id = $request->input("purchase_id");
            $purchase = Purchase::findOrFail($purchase_id);

            $consumables_json = $purchase->consumables_json;
            $consumables = json_decode($consumables_json, true);

            $quantity = $request->input("quantity");
            $purchase_cost = $request->input("purchase_cost");
            $assigned_type = "App\Models\Purchase";
            foreach ($consumables as &$consumable_json) {
                if ($consumable_json["consumable_id"] == $consumable->id) {
                    $quantity_in_json = $consumable_json["quantity"];

                    $reviewed = 0;
                    if (isset($consumable_json["reviewed"])) {
                        $reviewed = $consumable_json["reviewed"];
                    }
                    $max_quantity = $quantity_in_json - $reviewed;

                    if ($quantity > $max_quantity) {
                        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
                    } else {
                        $consumable_json["reviewed"] = $quantity + $reviewed;
                    }

                }
            }
            $purchase->consumables_json = json_encode($consumables);

//            if ($consumable->purchase_cost < $purchase_cost) {
//                $consumable->purchase_cost = $purchase_cost;
//            }
            $consumable->purchase_cost = $purchase_cost;
            $consumable->qty = $consumable->qty + $quantity;
            $consumable->locations()->attach($consumable->id, [
                'consumable_id' => $consumable->id,
                'user_id' => Auth::id(),
                'quantity' => $quantity,
                'cost' => $purchase_cost,
                'type' => ConsumableAssignment::PURCHASE,
                'assigned_to' => $purchase->id,
                'assigned_type' => $assigned_type,
            ]);


            if ($consumable->save()) {
                $purchase->checkStatus();
                $purchase->save();
                return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.update.success')));
            }

        } else {
            return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
        }


        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
    }


    /**
    * Gets a paginated collection for the select2 menus
    *
    * @see \App\Http\Transformers\SelectlistTransformer
    */
    public function selectlist(Request $request)
    {

        $consumables = Consumable::select([
            'consumables.id',
            'consumables.name',
            'consumables.model_number',
            'consumables.category_id',
            'consumables.location_id',
            'consumables.manufacturer_id',
            'consumables.qty',
        ])->with('manufacturer', 'category','location');


        if ($request->filled('search')) {
            $consumables = $consumables->SearchByManufacturerOrCatOrLocation($request->input('search'));
        }

        $consumables = $consumables->paginate(50);

        foreach ($consumables as $consumable) {
            $consumable->use_text = '';

            $consumable->use_text .= "[" . $consumable->numRemaining() . "] ";
            $consumable->use_text .= (($consumable->location) ? " (".e($consumable->location->name) . ') - ' : '');
            $consumable->use_text .= (($consumable->category) ? e($consumable->category->name) . ' - ' : '');

            $consumable->use_text .= (($consumable->manufacturer) ? e($consumable->manufacturer->name) . ' - ' : '');

            $consumable->use_text .= e($consumable->name) . ' - ';
            $consumable->use_text .= e($consumable->model_number);

        }
        if ($request->filled('assetStatusType') && $request->input('assetStatusType') === 'notnull') {
            return (new SelectlistTransformer)->transformSelectlistConsumables($consumables);
        } else {
            return (new SelectlistTransformer)->transformSelectlist($consumables);
        }


    }



//    /**
//     * Gets a paginated collection for the select2 menus
//     *
//     * @see \App\Http\Transformers\SelectlistTransformer
//     */
//    public function selectlist(Request $request)
//    {
//        $consumables = Consumable::select([
//            'consumables.id',
//            'consumables.name',
//        ]);
//
//        if ($request->filled('search')) {
//            $consumables = $consumables->where('consumables.name', 'LIKE', '%'.$request->get('search').'%');
//        }
//
//        $consumables = $consumables->orderBy('name', 'ASC')->paginate(50);
//
//        return (new SelectlistTransformer)->transformSelectlist($consumables);
//    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     * @since [v4.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function compact(Request $request, $id)
    {
        $this->authorize('edit', Consumable::class);
        $main_consumable = Consumable::findOrFail($id);

        if ($request->filled('id_array')) {

            $id_array= $request->input("id_array");

            $consumables = Consumable::findMany($id_array);
            ConsumableAssignment::whereIn('consumable_id', $id_array)->update(['consumable_id' => $main_consumable->id]);
            Actionlog::where("item_type","App\Models\Consumable")->whereNotIn("action_type",["create","delete","update"])->whereIn('item_id', $id_array)->update(['item_id' => $main_consumable->id]);
            $all_amount = 0;
            foreach ($consumables as &$consumable_delete) {
                $all_amount+=$consumable_delete->qty;
                $consumable_delete->delete();
            }

            $main_consumable->qty=$main_consumable->qty+$all_amount;


            if ($main_consumable->save()) {
                return response()->json(Helper::formatStandardApiResponse('success', $main_consumable, trans('admin/consumables/message.update.success')));
            }

        } else {
            return response()->json(Helper::formatStandardApiResponse('error', null, $main_consumable->getErrors()));
        }


        return response()->json(Helper::formatStandardApiResponse('error', null, $main_consumable->getErrors()));
    }
}