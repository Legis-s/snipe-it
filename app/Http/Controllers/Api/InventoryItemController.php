<?php

namespace App\Http\Controllers\Api;

use App\Actions\Inventories\UpdateInventoryItemAction;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\InventoryItemTransformer;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * This class controls all actions related to inventory items for
 * the Snipe-IT Asset Management application.
 */
class InventoryItemController extends Controller
{
    /**
     * Returns JSON listing of all inventory items
     */
    public function index(Request $request): JsonResponse|array
    {
        $this->authorize('view', Location::class);
        $inventory_items = InventoryItem::with('asset', 'inventory', 'status')
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
            ])
            ->with('adminuser');

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
                'id',
                'notes',
                'name',
                'model',
                'category',
                'manufacturer',
                'serial_number',
                'tag',
                'checked',
                'checked_at',
                'status_id',
                'created_at',
                'updated_at',
            ];

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        $inventory_items->orderBy($sort, $order);
        $total = $inventory_items->count();
        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($inventory_items) && ($request->get('offset') > $total)) ? $total : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $inventory_items = $inventory_items->skip($offset)->take($limit)->get();

        return (new InventoryItemTransformer)->transformInventoryItemsvsAsset($inventory_items, $total);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse|array
    {
        $this->authorize('view', Location::class);
        $inventory_item = InventoryItem::findOrFail($id);

        return (new InventoryItemTransformer)->transformInventoryItem($inventory_item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $item = UpdateInventoryItemAction::run((int) $id, $request->all());

        return response()->json(Helper::formatStandardApiResponse(
            'success', (new InventoryItemTransformer)->transformInventoryItem($item),
            trans('admin/locations/message.update.success')
        ));
    }
}
