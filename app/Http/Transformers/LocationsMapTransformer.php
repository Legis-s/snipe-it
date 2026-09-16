<?php

namespace App\Http\Transformers;

use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class LocationsMapTransformer
{
    public function transformCollectionForMap(Collection $locations): array
    {
        $array = [];
        foreach ($locations as $location) {
            if (! $location->active && $location->assets_count == 0) {

            } else {
                $feature = $this->transformForMap($location);
                if ($feature !== []) {
                    $array[] = $feature;
                }
            }
        }
        $objects_array['type'] = 'FeatureCollection';
        $objects_array['features'] = $array;

        return $objects_array;

    }

    public function transformForMap(?Location $location = null): array
    {
        if ($location) {
            $cords = array_map('trim', explode(',', (string) $location->coordinates));
            if (count($cords) !== 2 || ! is_numeric($cords[0]) || ! is_numeric($cords[1])) {
                return [];
            }

            $cords = array_map('floatval', $cords);
            if (! is_finite($cords[0]) || ! is_finite($cords[1]) || abs($cords[0]) > 90 || abs($cords[1]) > 180) {
                return [];
            }

            $count = (int) $location->checked_assets_count;
            $max = (int) $location->assets_count;

            $res = '808080';

            if ($max > 0 && $count == $max) {
                $res = '00FF00';
            }
            if ($max > 0 && $count != $max) {
                $res = 'FF0000';
            }
            if ($location->object_code == '455') {
                if ($location->active) {
                    $options = [
                        'iconColor' => '#'.$res,
                    ];
                } else {
                    $options = [
                        'iconColor' => '#'.$res,
                        'preset' => 'islands#circleIcon',
                    ];
                }
            } else {
                if ($location->active) {
                    $options = [
                        'iconColor' => '#'.$res,
                        'preset' => 'islands#dotIcon',
                    ];
                } else {
                    $options = [
                        'iconColor' => '#'.$res,
                        'preset' => 'islands#icon',
                    ];
                }
            }

            $array = [
                'id' => (int) $location->id,
                'type' => 'Feature',
                'code' => (int) $location->object_code,
                'assets_count' => $max,
                'checked_assets_count' => $count,
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => $cords,
                    'active' => e($location->active),
                ],
                'properties' => [
                    'balloonContentHeader' => e($location->name),
                    'balloonContentBody' => "<a target='_blank'  href='/locations/".$location->id."'>Открыть список</a><br><a target='_blank'  href='https://bitrix.legis-s.ru/crm/type/1032/details/".$location->bitrix_id."/'>Открыть Bitrix [".$location->bitrix_id.']</a><br>Адрес: '.e($location->address).'<br>Активов: '.e($location->assets_count).'<br>'.'Инвентаризированно: '.$location->checked_assets_count.'<br>',
                    'balloonContentFooter' => '',
                    'hintContent' => e($location->name),
                ],
                'options' => $options,
            ];

            return $array;
        } else {
            return [];
        }
    }
}
