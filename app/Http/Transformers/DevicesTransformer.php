<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Device;
use App\Models\Location;
use Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class DevicesTransformer
{
    public function transformDevices(Collection $devices, $total)
    {
        $array = [];
        foreach ($devices as $device) {
            $array[] = self::transformDevice($device);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformDevice(Device $device = null)
    {
        if ($device) {

            $array = [
                'id' => (int) $device->id,
                'number' =>  ($device->number) ? e($device->number) : null,
                'imei' =>  ($device->imei) ? e($device->imei) : null,
                'statusCode' =>  ($device->statusCode) ? e($device->statusCode) : null,
                'description' =>  ($device->description) ? e($device->description) : null,
                'batteryLevel' => (int) $device->batteryLevel,
                'model' =>  ($device->model) ? e($device->model) : null,
//                'asset' => ($device->asset) ? (new AssetsTransformer())->transformAsset($device->asset) : null,
                'asset' => ($device->asset) ? [
                    'id' => (int) $device->asset->id,
                    'name'=> e($device->asset->present()->fullName),
                ] : null,
                'lastUpdate' => Helper::getFormattedDateObject($device->lastUpdate, 'datetime'),
                'created_at' => Helper::getFormattedDateObject($device->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($device->updated_at, 'datetime'),
            ];


            return $array;
        }
    }

}
