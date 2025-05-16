<?php
namespace App\Http\Controllers\Api;


use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\InventoriesTransformer;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Location;
use Auth;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse | array
    {
//        $this->authorize('view', User::class);

        $inventories = Inventory::with('inventory_items','location')
            ->select([
                'inventories.id',
                'inventories.status',
                'inventories.name',
                'inventories.device',
                'inventories.responsible_id',
                'inventories.responsible',
                'inventories.responsible_photo',
                'inventories.coords',
                'inventories.log',
                'inventories.comment',
                'inventories.location_id',
                'inventories.created_at',
                'inventories.updated_at',
            ])
            ->withCount([
                'inventory_items as total',
                'inventory_items as checked' => function (Builder $query) {
                    $query->where('checked', true);
                },
                'inventory_items as successfully' => function (Builder $query) {
                    $query->where('successfully', true);
                },
            ]);

        if ($request->filled('location_id')) {
            $inventories->where('inventories.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('bitrix_id')) {
            $location = Location::where('bitrix_id', $request->input('bitrix_id'))->first();
            if($location){
                $inventories->where('inventories.location_id', '=', $location->id);
            } else{
                return response()->json(Helper::formatStandardApiResponse('error', null ));
            }
        }

        if ($request->filled('search')) {
            $inventories = $inventories->TextSearch($request->input('search'));
        }
        $allowed_columns =
            [
                'id','status','name','device','status','created_at',
                'updated_at'
            ];


        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        $inventories->orderBy($sort, $order);
        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($inventories) && ($request->get('offset') > $inventories->count())) ? $inventories->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');


        $total = $inventories->count();
        $inventories = $inventories->skip($offset)->take($limit)->get();
        return (new InventoriesTransformer)->transformInventories($inventories, $total);
    }

    /**
     * Display the specified resource.
     * @param  int  $id
     */
    public function show($id): JsonResponse | array
    {
//        $this->authorize('view', Location::class);

        $inventory = Inventory::with('inventory_items','location')
            ->select([
                'inventories.id',
                'inventories.status',
                'inventories.name',
                'inventories.device',
                'inventories.responsible_id',
                'inventories.responsible',
                'inventories.responsible_photo',
                'inventories.coords',
                'inventories.log',
                'inventories.comment',
                'inventories.location_id',
                'inventories.created_at',
                'inventories.updated_at',
            ])
            ->withCount([
                'inventory_items as total',
                'inventory_items as checked' => function (Builder $query) {
                    $query->where('checked', true);
                },
                'inventory_items as successfully' => function (Builder $query) {
                    $query->where('successfully', true);
                },
            ])
            ->findOrFail($id);

        return (new InventoriesTransformer)->transformInventory($inventory);
    }


    /**
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request) : JsonResponse
    {
        $data = $request->all();
        if (isset($data['bitrix_id'])){
            $location = Location::where('bitrix_id',$data["bitrix_id"] )->firstOrFail();
        }elseif (isset($data['location_id'])){
            $location = Location::where('id',$data["location_id"] )->firstOrFail();
        }else{
            return response()->json(Helper::formatStandardApiResponse('error'));
        }


        $assets = Asset::with('assignedTo','model',
                'model.category', 'model.manufacturer', 'assetstatus')->select([
            'assets.id',
            'assets.name',
            'assets.notes',
            'assets.asset_tag',
            'assets.status_id',
            'assets.model_id',
            'assets.location_id',
            'assets.serial',
            'assets.created_at',
            'assets.updated_at',
            'assets.deleted_at',
        ]);

//        $assets = Company::scopeCompanyables(Asset::select('assets.*'),"company_id","assets")
//            ->with('location', 'assetstatus', 'assetlog', 'company', 'defaultLoc','assignedTo',
//                'model.category', 'model.manufacturer', 'model.fieldset','supplier');
        $assets->where('assets.location_id', '=', $location->id);
        $assets->whereNull('assets.deleted_at');

        $assets->whereHas('assetstatus', function ($query) {
            $query->where('deployable', '=', 1)
                ->where('pending', '=', 0)
                ->where('archived', '=', 0);
        });


        $assets = $assets->get();

        $inventory = new Inventory;
        $inventory->name = $location->name ."_".date("d.m.Y H:i:s");
        $inventory->location_id = $location->id;
        $inventory->fill($request->all());


        if ($request['responsible_photo']){
            $destinationPath = public_path().'/uploads/inventories/';
            $file = base64_decode($inventory->responsible_photo);
            $filename = 'inventories-'.$inventory->id.'-'.str_random(8).".jpg";
            $success = file_put_contents($destinationPath.$filename, $file);
            if ($success>0){
                $inventory->responsible_photo = $filename;
            }
        }
        if ($inventory->save()) {
            foreach ($assets as &$asset) {
                $inventory_item = new InventoryItem;
                $inventory_item->asset_id = $asset->id;
                $inventory_item->name = $asset->name;
                $inventory_item->notes = $asset->notes;
                if ($asset->model && $asset->model->name){
                    $inventory_item->model = $asset->model->name;
                }
                if ($asset->model && $asset->model->manufacturer){
                    $inventory_item->manufacturer = $asset->model->manufacturer->name;
                }
                if ($asset->model && $asset->model->category){
                    $inventory_item->category = $asset->model->category->name;
                }
                $inventory_item->tag = $asset->asset_tag;
                $inventory_item->serial_number= $asset->serial;
                $inventory_item->inventory_id = $inventory->id;
                $inventory_item->save();
            }

            return response()->json(Helper::formatStandardApiResponse('success', (new InventoriesTransformer)->transformInventory($inventory,true), "Инвентаризация успешно создана"));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $inventory->getErrors()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id) : JsonResponse
    {
//        $this->authorize('update', Location::class);
        $inventory = Inventory::findOrFail($id);
        $inventory->fill($request->all());

        if ($request['responsible_photo']){
            $destinationPath = public_path().'/uploads/inventories/';
            $file = base64_decode($inventory->responsible_photo);
            $filename = 'inventories-'.$inventory->id.'-'.str_random(8).".jpg";
            $success = file_put_contents($destinationPath.$filename, $file);
            if ($success>0){
                $inventory->responsible_photo = $filename;
            }
        }

        if ($inventory->save()) {
            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new InventoriesTransformer)->transformInventory($inventory,true),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $inventory->getErrors()));
    }


    /**
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function clearallemply(Request $request): JsonResponse
    {
        $dayBefore = (new DateTime('now'))->format('Y-m-d');


        $inventories = Inventory::with('inventory_items','location')
            ->select([
                'inventories.id',
                'inventories.status',
                'inventories.name',
                'inventories.device',
                'inventories.responsible_id',
                'inventories.responsible',
                'inventories.responsible_photo',
                'inventories.coords',
                'inventories.log',
                'inventories.comment',
                'inventories.location_id',
                'inventories.created_at',
                'inventories.updated_at',
            ])
            ->withCount([
                'inventory_items as total',
                'inventory_items as checked' => function (Builder $query) {
                    $query->where('checked', true);
                },
                'inventory_items as successfully' => function (Builder $query) {
                    $query->where('successfully', true);
                },
            ])
            ->where('inventories.created_at', '<', $dayBefore)
            ->get();

        $to_delete  = 0;
        foreach ($inventories as &$inv) {
            $checked = $inv->checked;
            if ($checked == 0){
                $to_delete++;
                $inv->inventory_items()->forceDelete();
                $inv->forceDelete();
            }
        }
//        $text = "Получено " . count($inventories) . "  инвентаризаций\n К удалению  " . $to_delete . "  инвентаризаций\n";
//        return response()->json(Helper::formatStandardApiResponse('success', null,$text));
        return response()->json(Helper::formatStandardApiResponse('success', null,null));
    }

}