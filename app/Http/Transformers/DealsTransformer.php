<?php

namespace App\Http\Transformers;

use App\Models\Contract;
use App\Models\Deal;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Gate;
use App\Helpers\Helper;

class DealsTransformer
{
    public function transformDeals(Collection $deals, $total)
    {
        $array = array();
        foreach ($deals as $deal) {
            $array[] = self::transformDeal($deal);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformDeal(Deal $deal = null)
    {
        if ($deal) {

            $array = [
                'id' => (int)$deal->id,
                'name' => e($deal->name),
                'number' => e($deal->number),
//                'status' => e($deal->getStatusText()),
                'status' => e($deal->status),
                'type' => e($deal->getTypeText()),
                'assets_count'    => (int) $deal->assets_count,
                'assets_sum_purchase_cost'    => (int) $deal->assets_sum,
                'consumable_count'    => (int) $deal->consumable_count,
                'consumables_cost'    => (int) $deal->consumables_cost,
                'summ'    => (int) $deal->summ,
                'created_at' => Helper::getFormattedDateObject($deal->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($deal->updated_at, 'datetime'),
                'bitrix_id' => ($deal->bitrix_id) ? (int)$deal->bitrix_id : null,
            ];

            return $array;
        }
    }

}
