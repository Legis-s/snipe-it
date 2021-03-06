<?php

namespace App\Http\Transformers;

use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Gate;
use App\Helpers\Helper;

class LocationsTransformer
{
    public function redYellowGreen($min, $max, $value)
    {
        $green_max = 200;
        $red_max = 255;
        $red = 0;
        $green = 0;
        $blue = 0;

        if ($value < $max / 2) {
            $red = $red_max;
            $green = round(($value / ($max / 2)) * $green_max);
        } else {
            $green = $green_max;
            $red = round((1 - (($value - ($max / 2)) / ($max / 2))) * $red_max);
        }
        return dechex($red) . dechex($green). "00";
    }

    public function transformLocations(Collection $locations, $total)
    {
        $array = array();
        foreach ($locations as $location) {
            $array[] = self::transformLocation($location);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }


    public function transformLocation(Location $location = null)
    {
        if ($location) {

            $children_arr = [];
            foreach ($location->children as $child) {
                $children_arr[] = [
                    'id' => (int)$child->id,
                    'name' => $child->name
                ];
            }

            $array = [
                'id' => (int)$location->id,
                'name' => e($location->name),
                'image' => ($location->image) ? app('locations_upload_url') . e($location->image) : null,
                'address' => ($location->address) ? e($location->address) : null,
                'address2' => ($location->address2) ? e($location->address2) : null,
                'city' => ($location->city) ? e($location->city) : null,
                'state' => ($location->state) ? e($location->state) : null,
                'country' => ($location->country) ? e($location->country) : null,
                'zip' => ($location->zip) ? e($location->zip) : null,
                'assigned_assets_count' => (int)$location->assigned_assets_count,
                'assets_count' => (int)$location->assets_count,
                'users_count' => (int)$location->users_count,
                'currency' => ($location->currency) ? e($location->currency) : null,
                'created_at' => Helper::getFormattedDateObject($location->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($location->updated_at, 'datetime'),
                'parent' => ($location->parent) ? [
                    'id' => (int)$location->parent->id,
                    'name' => e($location->parent->name)
                ] : null,
                'manager' => ($location->manager) ? (new UsersTransformer)->transformUser($location->manager) : null,
                'bitrix_id' => ($location->bitrix_id) ? (int)$location->bitrix_id : null,
                'children' => $children_arr,
                'notes' => ($location->notes) ? e($location->notes) : null,
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Location::class) ? true : false,
                'delete' => (Gate::allows('delete', Location::class) && ($location->assigned_assets_count == 0) && ($location->assets_count == 0) && ($location->users_count == 0) && ($location->deleted_at == '')) ? true : false,
            ];

            $array += $permissions_array;

            return $array;
        }


    }

    public function transformCollectionForMap(Collection $locations)
    {
        $array = array();
        foreach ($locations as $location) {
            if (!$location->active &&$location->assets_count == 0 ){

            }else{
                $array[] = self::transformForMap($location);
            }
        }
        $objects_array['type'] = "FeatureCollection";
        $objects_array['features'] = $array;
        return $objects_array;

    }

    public function transformForMap(Location $location = null)
    {
        if ($location) {
            $cords = [];
            if ($location->coordinates) {
                $cords = explode(",", $location->coordinates);
            }
            $count = 0;
            $all_price= 0;
            if ($location->assets) {
                $assets = $location->assets;
                foreach ($assets as $asset) {
                    $all_price+= $asset->purchase_cost;
                    $asset_tag = $asset->asset_tag;
                    $first_s = substr($asset_tag, 0, 1);
                    if ($first_s == "I" || $first_s == "X" || strlen($asset_tag) > 8) {
                        $count++;
                    }
                }
            }
            $max = $location->assets_count;
            $res = "808080";
            if ($count > 0 && $max > 0) {
                $res = self::redYellowGreen(0, $max,$count);
            }
            if ($max > 0 && $count==0){
                $res = "FF0000";
            }
            if ($max != 0 && $max == $count){
                $res = "00FF00";
            }
            if ($location->active){
                $options = [
                    "iconColor" => '#'.$res,
                ];
            }else{
                $options = [
                    "iconColor" => '#'.$res,
                    "preset"=> 'islands#circleIcon',
                ];
            }
            $array = [
                "id" => (int)$location->id,
                "type" => "Feature",
                "geometry" => [
                    "type" => "Point",
                    "coordinates" => $cords,
                    "active"=> e($location->active)
                ],
                "properties" => [
                    "balloonContentHeader" => e($location->name),
                    "balloonContentBody" => "<a target='_blank'  href='/locations/".$location->id."'>Открыть список</a><br><a target='_blank'  href='https://bitrix.legis-s.ru/crm/object/details/".$location->bitrix_id."/'>Открыть Bitrix [".$location->bitrix_id."]</a><br>Адрес: " . e($location->address) ."<br>Общяя стоимость: ".$all_price." руб. <br>Активов: " . e($location->assets_count) . "<br>" . "Инвентаризированно: " . $count. "<br>",
                    "balloonContentFooter" => "",
                    "hintContent" => e($location->name)
                ],
                "options" => $options
            ];
            return $array;
        } else {
            return [];
        }

    }

}
