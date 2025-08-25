<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Inventory;
use Gate;
use Illuminate\Database\Eloquent\Collection;

class InventoriesTransformer
{

    public function transformInventories(Collection $inventories, $total)
    {
        $array = array();
        foreach ($inventories as $inventory) {
            $array[] = self::transformInventory($inventory);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformInventory(Inventory $inventory, $full = false)
    {


        $inventory_items_arr = [];
        if ($full) {
            foreach ($inventory->inventory_items as $inventory_item) {
                $inventory_items_arr[] = (new InventoryItemTransformer)->transformInventoryItem($inventory_item);
            }
        }


        $array = [
            'id' => (int)$inventory->id,
            'status' => $inventory->status ? e($inventory->status) : null,
            'status_text' => $inventory->status
                ? Helper::getFormattedStatus($inventory->status)
                : "",
            'name' => e($inventory->name),
            'device' => $inventory->device ? e($inventory->device) : null,
            'responsible' => $inventory->responsible ? e($inventory->responsible) : null,
            'responsible_photo' => $inventory->responsible_photo ? e($inventory->responsible_photo_url()) : null,
            'coords' => $inventory->coords ? e($inventory->coords) : null,
            'log' => $inventory->log ? e($inventory->log) : null,
            'comment' => $inventory->comment ? e($inventory->comment) : null,
            'created_at' => Helper::getFormattedDateObject($inventory->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($inventory->updated_at, 'datetime'),
            'total' => (int)$inventory->total,
            'checked' => (int)$inventory->checked,
            'successfully' => (int)$inventory->successfully,
            'location' => $inventory->location
                ? (new LocationsTransformer)->transformLocation($inventory->location)
                : null,
            'inventory_items' => $inventory_items_arr,
        ];
        $permissions_array['available_actions'] = [
            'delete' => (Gate::allows('superadmin')) ? true : false,
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformInventoriesDatatable($inventories)
    {
        return (new DatatablesTransformer)->transformDatatables($inventories);
    }

}
