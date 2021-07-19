<?php

namespace App\Http\Transformers;

use App\Models\Contract;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Gate;
use App\Helpers\Helper;

class ContractsTransformer
{
    public function transformContracts(Collection $contracts, $total)
    {
        $array = array();
        foreach ($contracts as $contract) {
            $array[] = self::transformContract($contract);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformContract(Contract $contract = null)
    {
        if ($contract) {

            $array = [
                'id' => (int)$contract->id,
                'name' => e($contract->name),
                'number' => e($contract->number),
                'status' => e($contract->getStatusText()),
                'type' => e($contract->getTypeText()),
                'created_at' => Helper::getFormattedDateObject($contract->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($contract->updated_at, 'datetime'),
                'bitrix_id' => ($contract->bitrix_id) ? (int)$contract->bitrix_id : null,
            ];

            return $array;
        }
    }

}
