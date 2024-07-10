<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Location;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class LocationsTransformer
{
    public function transformLocations(Collection $locations, $total)
    {
        $array = [];
        foreach ($locations as $location) {
            $array[] = self::transformLocation($location);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLocation(Location $location = null)
    {
        if ($location) {
            $children_arr = [];
            if (! is_null($location->children)) {
                foreach ($location->children as $child) {
                    $children_arr[] = [
                        'id' => (int) $child->id,
                        'name' => $child->name,
                    ];
                }
            }

            $array = [
                'id' => (int) $location->id,
                'name' => e($location->name),
                'image' =>   ($location->image) ? Storage::disk('public')->url('locations/'.e($location->image)) : null,
                'address' =>  ($location->address) ? e($location->address) : null,
                'address2' =>  ($location->address2) ? e($location->address2) : null,
                'city' =>  ($location->city) ? e($location->city) : null,
                'state' =>  ($location->state) ? e($location->state) : null,
                'country' => ($location->country) ? e($location->country) : null,
                'zip' => ($location->zip) ? e($location->zip) : null,
                'phone' => ($location->phone!='') ? e($location->phone): null,
                'fax' => ($location->fax!='') ? e($location->fax): null,
                'assigned_assets_count' => (int) $location->assigned_assets_count,
                'assets_count'    => (int) $location->assets_count,
                'rtd_assets_count'    => (int) $location->rtd_assets_count,
                'users_count'    => (int) $location->users_count,
                'currency' =>  ($location->currency) ? e($location->currency) : null,
                'ldap_ou' =>  ($location->ldap_ou) ? e($location->ldap_ou) : null,
                'created_at' => Helper::getFormattedDateObject($location->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($location->updated_at, 'datetime'),
                'parent' => ($location->parent) ? [
                    'id' => (int) $location->parent->id,
                    'name'=> e($location->parent->name),
                ] : null,
                'manager' => ($location->manager) ? (new UsersTransformer)->transformUser($location->manager) : null,

                'bitrix_id' => ($location->bitrix_id) ? (int)$location->bitrix_id : null,
                'pult_id' => ($location->pult_id) ? (int)$location->pult_id : null,
                'children' => $children_arr,
                'notes' => ($location->notes) ? e($location->notes) : null,
                'sklad' => ($location->sklad) ? e($location->sklad) : null,
                'active' => ($location->active) ? e($location->active) : null,
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Location::class) ? true : false,
                'delete' => $location->isDeletable(),
                'bulk_selectable' => [
                    'delete' => $location->isDeletable()
                ],
                'clone' => (Gate::allows('create', Location::class) && ($location->deleted_at == '')),
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
            $count = $location->checked_assets_count;
            $max = $location->assets_count;

            $res = "808080";


            if ($max>0 && $count ==  $max){
                $res = "00FF00";
            }
            if ($max>0 && $count !=  $max){
                $res = "FF0000";
            }
            if ($location->object_code == "455"){
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
            }else{
                if ($location->active){
                    $options = [
                        "iconColor" => '#'.$res,
                        "preset"=> 'islands#dotIcon',
                    ];
                }else{
                    $options = [
                        "iconColor" => '#'.$res,
                        "preset"=> 'islands#icon',
                    ];
                }
            }

            $array = [
                "id" => (int)$location->id,
                "type" => "Feature",
                "code"=> (int)$location->object_code,
                "assets_count"=> $location->assets_count,
                "checked_assets_count"=> $location->checked_assets_count,
                "geometry" => [
                    "type" => "Point",
                    "coordinates" => $cords,
                    "active"=> e($location->active)
                ],
                "properties" => [
                    "balloonContentHeader" => e($location->name),
                    "balloonContentBody" => "<a target='_blank'  href='/locations/".$location->id."'>Открыть список</a><br><a target='_blank'  href='https://bitrix.legis-s.ru/crm/object/details/".$location->bitrix_id."/'>Открыть Bitrix [".$location->bitrix_id."]</a><br>Адрес: " . e($location->address) ."<br>Активов: " . e($location->assets_count) . "<br>" . "Инвентаризированно: " .  $location->checked_assets_count. "<br>",
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
