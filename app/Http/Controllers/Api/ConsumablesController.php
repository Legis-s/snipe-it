<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\SelectlistTransformer;
use App\Models\Asset;
use App\Models\Component;
use App\Models\ConsumableAssignment;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Purchase;
use DebugBar\DebugBar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Consumable;
use App\Http\Transformers\ConsumablesTransformer;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ConsumablesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @since [v4.0]
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function index(Request $request)
    {
        $this->authorize('index', Consumable::class);
        $consumables = Company::scopeCompanyables(
            Consumable::select('consumables.*')
//                ->with('company', 'location', 'category', 'users', 'manufacturer')
                ->with('company', 'location', 'category', 'locations', 'manufacturer', 'model.category', 'model.manufacturer')
        );

        if ($request->filled('search')) {
            $consumables = $consumables->TextSearch(e($request->input('search')));
        }

        if ($request->filled('company_id')) {
            $consumables->where('company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('company_id')) {
            $consumables->where('company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('purchase_id')) {
            $consumables->where('purchase_id', '=', $request->input('purchase_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $consumables->where('manufacturer_id', '=', $request->input('manufacturer_id'));
        }


        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($consumables) && ($request->get('offset') > $consumables->count())) ? $consumables->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $allowed_columns = ['id', 'name', 'order_number', 'min_amt', 'purchase_date', 'purchase_cost', 'company', 'category', 'model_number', 'item_no', 'manufacturer', 'location', 'qty', 'image'];
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';


        switch ($sort) {
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
            default:
                $consumables = $consumables->orderBy($sort, $order);
                break;
        }


        $total = $consumables->count();
        $consumables = $consumables->skip($offset)->take($limit)->get();
        return (new ConsumablesTransformer)->transformConsumables($consumables, $total);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function store(Request $request)
    {
        $this->authorize('create', Consumable::class);
        $consumable = new Consumable;
        $consumable->fill($request->all());
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
     * @param int $id
     * @return \Illuminate\Http\Response
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function show($id)
    {
        $this->authorize('view', Consumable::class);
        $consumable = Consumable::findOrFail($id);
        return (new ConsumablesTransformer)->transformConsumable($consumable);
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
    public function update(Request $request, $id)
    {
        $this->authorize('update', Consumable::class);
        $consumable = Consumable::findOrFail($id);
        $consumable->fill($request->all());

        if ($consumable->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
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
        $this->authorize('review', Consumable::class);
        $consumable = Consumable::findOrFail($id);
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

                    $reviewed=0;
                    if (isset($consumable_json["reviewed"])){
                        $reviewed = $consumable_json["reviewed"];
                    }
                    $max_quantity = $quantity_in_json - $reviewed;

                    if ($quantity>$max_quantity){
                        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
                    }else{
                        $consumable_json["reviewed"] = $quantity+$reviewed;
                    }

                }
            }
            $purchase->consumables_json = json_encode($consumables);

            if ($consumable->purchase_cost < $purchase_cost){
                $consumable->purchase_cost = $purchase_cost;
            }
            $consumable->qty =$consumable->qty+$quantity;
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
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     *
     */
    public function selectlist(Request $request)
    {

        $this->authorize('index', Consumable::class);

        $consumables = Consumable::select([
            'consumables.id',
            'consumables.name',
            'consumables.category_id',
            'consumables.manufacturer_id',
        ])->with('manufacturer','category');


        if ($request->filled('search')) {
            $consumables = $consumables->where('consumables.name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $consumables = $consumables->orderBy('name', 'ASC')->paginate(50);
        foreach ($consumables as $consumable) {

            $consumable->use_text = '';

            $consumable->use_text .= (($consumable->category) ? e($consumable->category->name).' - ' : '');

            $consumable->use_text .= (($consumable->manufacturer) ? e($consumable->manufacturer->name).' - ' : '');

            $consumable->use_text .=  e($consumable->name);

       }

        return (new SelectlistTransformer)->transformSelectlist($consumables);


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
    public function compact(Request $request, $id)
    {
        $this->authorize('edit', Consumable::class);
        $main_consumable = Consumable::findOrFail($id);

        if ($request->filled('id_array')) {

            $id_array= $request->input("id_array");

            $consumables = Consumable::findMany($id_array);
            ConsumableAssignment::whereIn('consumable_id', $id_array)->update(['consumable_id' => $main_consumable->id]);

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
