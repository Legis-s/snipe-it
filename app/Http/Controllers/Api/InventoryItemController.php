<?php
namespace App\Http\Controllers\Api;


use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\InventoryItemTransformer;
use App\Models\Inventory;
use App\Models\InventoryItem;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Image;

/**
 * This class controls all actions related to inventory items for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class InventoryItemController extends Controller
{

    /**
     * Returns JSON listing of all inventory items
     */
    public function index(Request $request) : JsonResponse | array
    {
        $inventory_items = InventoryItem::with('asset','inventory','status')
            ->select([
                'inventory_items.id',
                'inventory_items.notes',
                'inventory_items.name',
                'inventory_items.model',
                'inventory_items.category',
                'inventory_items.manufacturer',
                'inventory_items.serial_number',
                'inventory_items.tag',
                'inventory_items.photo',
                'inventory_items.checked',
                'inventory_items.checked_at',
                'inventory_items.inventory_id',
                'inventory_items.asset_id',
                'inventory_items.status_id',
                'inventory_items.created_at',
                'inventory_items.updated_at',
                'inventory_items.successfully',
            ]);

        if ($request->filled('inventory_id')) {
            $inventory_items->where('inventory_items.inventory_id', '=', $request->input('inventory_id'));
        }

        if ($request->filled('asset_id')) {
            $inventory_items->where('inventory_items.asset_id', '=', $request->input('asset_id'));
        }

        if ($request->filled('search')) {
            $inventory_items = $inventory_items->TextSearch($request->input('search'));
        }

        $allowed_columns =
            [
                'id','notes','name','model','category','manufacturer','serial_number','tag','checked','checked_at','status_id','created_at', 'updated_at'
            ];


        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        $inventory_items->orderBy($sort, $order);
        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($inventory_items) && ($request->get('offset') > $inventory_items->count())) ? $inventory_items->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');


        $total = $inventory_items->count();
        $inventory_items = $inventory_items->skip($offset)->take($limit)->get();

        return (new InventoryItemTransformer)->transformInventoryItemsvsAsset($inventory_items, $total);
    }


    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse | array
    {
        $inventory_item = InventoryItem::findOrFail($id);
        return (new InventoryItemTransformer)->transformInventoryItem($inventory_item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $inventory_item = InventoryItem::findOrFail($id);
        $inventory_item->fill($request->all());

        if ($request['photo']){
            $destinationPath = public_path().'/uploads/inventory_items/';

            $file = base64_decode($inventory_item->photo);
            $filename = 'items-'.$inventory_item->id.'-'.str_random(8).".jpg";
            $success = file_put_contents($destinationPath.$filename, $file);
            if ($success>0){
                $inventory_item->photo = $filename;
            }
        }
        if ($inventory_item->status){
            if($inventory_item->status->success){
                $inventory_item->successfully = true;
            }
        }

        if ($inventory_item->save()) {
            if ($inventory_item->checked == true){
                $asset = $inventory_item->asset;
                $asset->last_audit_date = date('Y-m-d h:i:s');
                $asset->save();
            }

            /** @var Inventory $inventory */
            $inventory  = $inventory_item->inventory;
            $inventory_items = $inventory->inventory_items;
            $finished = true;
            foreach ($inventory_items as $item) {
                /** @var InventoryItem $item */
                if  ($item->checked == false){
                    $finished = false;
                    break;
                }
            }
            if ($finished){
                $inventory->status = "FINISH_OK";
                $inventory->save();
            }

            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new InventoryItemTransformer)->transformInventoryItem($inventory_item),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $inventory_item->getErrors()));
    }
}