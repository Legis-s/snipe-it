<?php
namespace App\Http\Controllers\Api;


use App\Http\Transformers\DevicesTransformer;
use App\Http\Transformers\InventoriesTransformer;
use App\Http\Transformers\LocationsTransformer;
use App\Models\Device;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Transformers\InventoryItemTransformer;
use App\Models\Company;
use App\Models\User;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Helpers\Helper;
use App\Http\Requests\SaveUserRequest;
use App\Models\Asset;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Http\Transformers\AccessoriesTransformer;
use App\Http\Transformers\LicensesTransformer;
use Auth;
use App\Models\AssetModel;
use Image;

class DevicesController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
//        $this->authorize('view', User::class);

        $devices= Device::with('asset')
            ->withTrashed()
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
                'devices.created_at',
                'devices.updated_at',
            ]);

        if ($request->filled('search')) {
            $devices = $devices->TextSearch($request->input('search'));
        }

        $allowed_columns =
            [
                'id',"number",'publicIp','enrollTime','imei','statusCode','description','batteryLevel','model','androidVersion','biometrikaVersion','launcherVersion','lastUpdate','asset_id','asset_sim_id','coordinates','locationUpdate','distance','created_at', 'updated_at'
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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
//        $this->authorize('view', Location::class);
        $inventory_item = InventoryItem::findOrFail($id);
        return (new InventoryItemTransformer)->transformInventoryItem($inventory_item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
//        $this->authorize('update', Location::class);
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
                $inventory_item->successfully= true;
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