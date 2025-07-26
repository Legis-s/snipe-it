<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\DevicesTransformer;
use App\Http\Transformers\InventoryItemTransformer;
use App\Models\Device;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevicesController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|array
    {
        $devices = Device::with('asset')
            ->select([
                'devices.id',
                'devices.number',
                'devices.imei',
                'devices.statusCode',
                'devices.description',
                'devices.batteryLevel',
                'devices.model',
                'devices.androidVersion',
                'devices.biometrikaVersion',
                'devices.launcherVersion',
                'devices.serial',
                'devices.lastUpdate',
                'devices.asset_id',
                'devices.asset_sim_id',
                'devices.coordinates',
                'devices.locationUpdate',
                'devices.distance',
                'devices.enrollTime',
                'devices.publicIp',
                'devices.anyDesk',
                'devices.created_at',
                'devices.updated_at',
            ]);

        if ($request->filled('search')) {
            $devices = $devices->TextSearch($request->input('search'));
        }

        $allowed_columns =
            [
                'id', "number", 'publicIp', 'enrollTime', 'imei', 'statusCode', 'description', 'batteryLevel', 'model', 'androidVersion', 'biometrikaVersion', 'launcherVersion', 'lastUpdate', 'asset_id', 'asset_sim_id', 'coordinates', 'locationUpdate', 'distance', 'created_at', 'updated_at'
            ];


        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        $devices->orderBy($sort, $order);
        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($devices) && ($request->get('offset') > $devices->count())) ? $devices->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');


        $total = $devices->count();
        $devices = $devices->skip($offset)->take($limit)->get();

        return (new DevicesTransformer())->transformDevices($devices, $total);
    }


    /**
     * Display the specified resource.
     * @param int $id
     */
    public function show($id): JsonResponse|array
    {
        $inventory_item = InventoryItem::findOrFail($id);
        return (new InventoryItemTransformer)->transformInventoryItem($inventory_item);
    }

}