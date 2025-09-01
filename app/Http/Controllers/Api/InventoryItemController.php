<?php
namespace App\Http\Controllers\Api;


use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\InventoryItemTransformer;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryStatuslabel;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $inventory_item = InventoryItem::with(['asset','inventory','status'])->findOrFail($id);
        $payload = $request->except(['status_id', 'asset', 'inventory', 'photo']);
        $inventory_item->fill($payload);


        if ($request->filled('photo')) {
            $raw = $request->input('photo'); // base64
            $file = base64_decode($raw, true);
            if ($file !== false) {
                $filename = 'items-'.$inventory_item->id.'-'.Str::random(8).'.jpg';
                Storage::disk('public')->put('uploads/inventory_items/'.$filename, $file);
                $inventory_item->photo = $filename;
            }
        }

        if ($request->filled('status_id')) {
            $inventory_item->status_id = $request->input('status_id');
        }

        if ($inventory_item->status_id){
            if($inventory_item->status_id == 4 && str_starts_with($inventory_item->asset->asset_tag, 'it_') ) {
                $inventory_item->status_id = 1;
            }
            if($inventory_item->status_id == 1){
                $inventory_item->successfully = true;
            }
        }

        $label = InventoryStatuslabel::findOrFail($inventory_item->status_id);
        $inventory_item->status()->associate($label);

        if ($inventory_item->save()) {
            if ($inventory_item->checked){
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
                if  (!$item->checked){
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