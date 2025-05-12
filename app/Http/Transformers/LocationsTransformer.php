<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
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
                'accessories_count' => (int) $location->accessories_count,
                'assigned_accessories_count' => (int) $location->assigned_accessories_count,
                'assigned_assets_count' => (int) $location->assigned_assets_count,
                'assets_count'    => (int) $location->assets_count,
                'rtd_assets_count'    => (int) $location->rtd_assets_count,
                'users_count'    => (int) $location->users_count,
                'currency' =>  ($location->currency) ? e($location->currency) : null,
                'ldap_ou' =>  ($location->ldap_ou) ? e($location->ldap_ou) : null,
                'notes' => Helper::parseEscapedMarkedownInline($location->notes),
                'created_at' => Helper::getFormattedDateObject($location->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($location->updated_at, 'datetime'),
                'parent' => ($location->parent) ? [
                    'id' => (int) $location->parent->id,
                    'name'=> e($location->parent->name),
                ] : null,
                'manager' => ($location->manager) ? (new UsersTransformer)->transformUser($location->manager) : null,
                'company' => ($location->company) ? [
                    'id' => (int) $location->company->id,
                    'name'=> e($location->company->name)
                ] : null,

                'children' => $children_arr,

                'bitrix_id' => ($location->bitrix_id) ? (int)$location->bitrix_id : null,
                'bitrix_id_old' => ($location->bitrix_id_old) ? (int)$location->bitrix_id_old : null,
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


    public function transformCheckedoutAccessories($accessory_checkouts, $total)
    {

        $array = [];
        foreach ($accessory_checkouts as $checkout) {
            $array[] = self::transformCheckedoutAccessory($checkout);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }


    public function transformCheckedoutAccessory(AccessoryCheckout $accessory_checkout)
    {

            $array = [
                'id' => $accessory_checkout->id,
                'assigned_to' => $accessory_checkout->assigned_to,
                'accessory' => $this->transformAccessory($accessory_checkout->accessory),
                'image' => ($accessory_checkout?->accessory?->image) ? Storage::disk('public')->url('accessories/' . e($accessory_checkout->accessory->image)) : null,
                'note' => $accessory_checkout->note ? e($accessory_checkout->note) : null,
                'created_by' => $accessory_checkout->adminuser ? [
                    'id' => (int) $accessory_checkout->adminuser->id,
                    'name'=> e($accessory_checkout->adminuser->present()->fullName),
                ]: null,
                'created_at' => Helper::getFormattedDateObject($accessory_checkout->created_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'checkout' => false,
                'checkin' => Gate::allows('checkin', Accessory::class),
            ];

            $array += $permissions_array;
        return $array;
    }



    /**
     * This gives a compact view of the location data without any additional relational queries,
     * allowing us to 1) deliver a smaller payload and 2) avoid additional queries on relations that
     * have not been easy/lazy loaded already
     *
     * @param Location $location
     * @return array
     * @throws \Exception
     */
    public function transformLocationCompact(Location $location = null)
    {
        if ($location) {

            $array = [
                'id' => (int) $location->id,
                'image' =>   ($location->image) ? Storage::disk('public')->url('locations/'.e($location->image)) : null,
                'type' => "location",
                'name' => e($location->name),
                'created_by' => $location->adminuser ? [
                    'id' => (int) $location->adminuser->id,
                    'name'=> e($location->adminuser->present()->fullName),
                ]: null,
                'created_at' => Helper::getFormattedDateObject($location->created_at, 'datetime'),
            ];

            return $array;
        }
    }

    private function transformAccessory(?Accessory $accessory): ?array
    {
        if ($accessory) {
            return [
                'id' => $accessory->id,
                'name' => $accessory->name,
            ];
        }

        return null;
    }

    public function transformCollectionForMap(Collection $locations): array
    {
        $array = array();
        foreach ($locations as $location) {
            if (!$location->active && $location->assets_count == 0) {

            } else {
                $array[] = self::transformForMap($location);
            }
        }
        $objects_array['type'] = "FeatureCollection";
        $objects_array['features'] = $array;
        return $objects_array;

    }

    public function transformForMap(Location $location = null): array
    {
        if ($location) {
            $cords = [];
            if ($location->coordinates) {
                $cords = explode(",", $location->coordinates);
            }
            $count = 0;
            $all_price = 0;
            $count = $location->checked_assets_count;
            $max = $location->assets_count;

            $res = "808080";


            if ($max > 0 && $count == $max) {
                $res = "00FF00";
            }
            if ($max > 0 && $count != $max) {
                $res = "FF0000";
            }
            if ($location->object_code == "455") {
                if ($location->active) {
                    $options = [
                        "iconColor" => '#' . $res,
                    ];
                } else {
                    $options = [
                        "iconColor" => '#' . $res,
                        "preset" => 'islands#circleIcon',
                    ];
                }
            } else {
                if ($location->active) {
                    $options = [
                        "iconColor" => '#' . $res,
                        "preset" => 'islands#dotIcon',
                    ];
                } else {
                    $options = [
                        "iconColor" => '#' . $res,
                        "preset" => 'islands#icon',
                    ];
                }
            }

            $array = [
                "id" => (int)$location->id,
                "type" => "Feature",
                "code" => (int)$location->object_code,
                "assets_count" => $location->assets_count,
                "checked_assets_count" => $location->checked_assets_count,
                "geometry" => [
                    "type" => "Point",
                    "coordinates" => $cords,
                    "active" => e($location->active)
                ],
                "properties" => [
                    "balloonContentHeader" => e($location->name),
                    "balloonContentBody" => "<a target='_blank'  href='/locations/" . $location->id . "'>Открыть список</a><br><a target='_blank'  href='https://bitrix.legis-s.ru/crm/type/1032/details/" . $location->bitrix_id . "/'>Открыть Bitrix [" . $location->bitrix_id . "]</a><br>Адрес: " . e($location->address) . "<br>Активов: " . e($location->assets_count) . "<br>" . "Инвентаризированно: " . $location->checked_assets_count . "<br>",
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
