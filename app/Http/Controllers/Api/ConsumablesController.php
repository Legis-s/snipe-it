<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\SelectlistTransformer;
use App\Models\Asset;
use App\Models\Component;
use App\Models\ConsumableAssignment;
use App\Models\Contract;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Consumable;
use App\Http\Transformers\ConsumablesTransformer;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $consumables = Company::scopeCompanyables(
            Consumable::select('consumables.*')
//                ->with('company', 'location', 'category', 'users', 'manufacturer')
                ->with('company', 'location', 'category', 'locations', 'manufacturer', 'model.category', 'model.manufacturer')
        );

        if ($request->filled('search')) {
            $consumables = $consumables->TextSearch(e($request->input('search')));
        }

        if ($request->filled('company_id')) {
            $consumables->where('company_id','=',$request->input('company_id'));
        }

        if ($request->filled('company_id')) {
            $consumables->where('company_id','=',$request->input('company_id'));
        }

        if ($request->filled('purchase_id')) {
            $consumables->where('purchase_id','=',$request->input('purchase_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $consumables->where('manufacturer_id','=',$request->input('manufacturer_id'));
        }


        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($consumables) && ($request->get('offset') > $consumables->count())) ? $consumables->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $allowed_columns = ['id','name','order_number','min_amt','purchase_date','purchase_cost','company','category','model_number', 'item_no', 'manufacturer','location','qty','image'];
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
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $id
     * @return \Illuminate\Http\Response
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
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
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
        return response()->json(Helper::formatStandardApiResponse('success', null,  trans('admin/consumables/message.delete.success')));
    }

//        /**
//    * Returns a JSON response containing details on the users associated with this consumable.
//    *
//    * @author [A. Gianotto] [<snipe@snipe.net>]
//    * @see ConsumablesController::getView() method that returns the form.
//    * @since [v1.0]
//    * @param int $consumableId
//    * @return array
//     */
//    public function getDataView($consumableId)
//    {
//        $consumable = Consumable::with(array('consumableAssignments'=>
//        function ($query) {
//            $query->orderBy($query->getModel()->getTable().'.created_at', 'DESC');
//        },
//        'consumableAssignments.admin'=> function ($query) {
//        },
//        'consumableAssignments.location'=> function ($query) {
//        },
//        ))->find($consumableId);
//
//        if (!Company::isCurrentUserHasAccess($consumable)) {
//            return ['total' => 0, 'rows' => []];
//        }
//        $this->authorize('view', Consumable::class);
//        $rows = array();
//
////        foreach ($consumable->consumableAssignments as $consumable_assignment) {
////            $rows[] = [
////                'name' => ($consumable_assignment->user) ? $consumable_assignment->user->present()->nameUrl() : 'Deleted User',
////                'created_at' => Helper::getFormattedDateObject($consumable_assignment->created_at, 'datetime'),
////                'admin' => ($consumable_assignment->admin) ? $consumable_assignment->admin->present()->nameUrl() : '',
////            ];
////        }
//
//        foreach ($consumable->consumableAssignments as $consumable_assignment) {
//            $rows[] = [
//                'name' => ($consumable_assignment->location) ? $consumable_assignment->location->present()->nameUrl() : 'Deleted Location',
//                'created_at' => Helper::getFormattedDateObject($consumable_assignment->created_at, 'datetime'),
//                'quantity' =>($consumable_assignment->quantity) ? $consumable_assignment->quantity: '',
//                'admin' => ($consumable_assignment->admin) ? $consumable_assignment->admin->present()->nameUrl() : '',
//            ];
//        }
//
//        $consumableCount = $consumable->locations->count();
//        $data = array('total' => $consumableCount, 'rows' => $rows);
//        return $data;
//    }
//
//    /**
//     * Returns a JSON response containing details on the users associated with this consumable.
//     *
//     * @author [A. Gianotto] [<snipe@snipe.net>]
//     * @see ConsumablesController::getDataViewLocation() method that returns the form.
//     * @since [v1.0]
//     * @param int $locationId
//     * @return array
//     */
//    public function getDataViewLocation($locationId)
//    {
//
//
//
////        $location = Location::findOrFail($locationId);
//        $consumableAssignments = ConsumableAssignment::where('assigned_to', $locationId)->get();
//
//        $components = ConsumableAssignment::scopeCompanyables(Component::select('components.*')
//            ->with('company', 'location', 'category'));
////        $location = Location::with(array('consumables'=>
////            function ($query) {
////                $query->orderBy($query->getModel()->getTable().'.created_at', 'DESC');
////            },
////            'consumables.admin'=> function ($query) {
////            },
////            'consumables.location'=> function ($query) {
////            },
//////            'consumables.consumable'=> function ($query) {
//////            },
////        ))->find($locationId);
//
//        $this->authorize('view', Consumable::class);
//        $rows = array();
//
//
//        foreach ($consumableAssignments as $consumable_assignment) {
//            $rows[] = [
////                'name' => ($consumable_assignment->location) ? $consumable_assignment->location->present()->nameUrl() : 'Deleted Location',
//                'name' => ($consumable_assignment->consumable) ? $consumable_assignment->consumable->present()->nameUrl() : 'Deleted Location',
//                'created_at' => Helper::getFormattedDateObject($consumable_assignment->created_at, 'datetime'),
//                'quantity' =>($consumable_assignment->quantity) ? $consumable_assignment->quantity: '',
//                'admin' => ($consumable_assignment->admin) ? $consumable_assignment->admin->present()->nameUrl() : '',
//            ];
//        }
//
////        $locationCount = $location->consumables->count();
//        $locationCount = $consumableAssignments->count();
//        $data = array('total' => $locationCount, 'rows' => $rows);
//        return $data;
//    }




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

        $consumables = Consumable::select([
            'consumables.id',
            'consumables.name',
        ]);

        $page = 1;
        if ($request->filled('page')) {
            $page = $request->input('page');
        }

        if ($request->filled('search')) {
            $consumables = $consumables->where('consumables.name', 'LIKE', '%'.$request->input('search').'%');
        }

        $consumables = $consumables->orderBy('name', 'ASC')->get();


        $paginated_results =  new LengthAwarePaginator($consumables->forPage($page, 500), $consumables->count(), 500, $page, []);

        return (new SelectlistTransformer)->transformSelectlist($paginated_results);

    }
}
