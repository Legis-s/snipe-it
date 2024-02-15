<?php
namespace App\Http\Transformers;

use App\Models\MassOperation;
use App\Models\Setting;
use Gate;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\Helper;

class MassOperationsTransformer
{

    public function transformMassOperations (Collection $massOperations, $total)
    {
        $array = array();
        foreach ($massOperations as $mo) {
            $array[] = self::transformMassOperation($mo);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformMassOperation (MassOperation $mo)
    {

        $array = [
            'id' => (int) $mo->id,
            'action_type'   => $mo->present()->actionType(),
            'name' => e($mo->name),
            'bitrix_task_id' => ($mo->bitrix_task_id) ? (int)$mo->bitrix_task_id : null,
            'assets_count' => e($mo->assets_count),
            'consumables_count' => e($mo->consumables_count),
            'assigned_to' => $this->transformAssignedTo($mo),
            'user' => ($mo->user) ? (new UsersTransformer)->transformUser($mo->user) : null,
            'notes' => ($mo->notes) ? e($mo->notes) : null,
            'created_at' => Helper::getFormattedDateObject($mo->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($mo->updated_at, 'datetime'),

        ];

        return $array;
    }


    public function transformAssignedTo($asset)
    {
        if ($asset->checkedOutToUser()) {
            return $asset->assigned ? [
                'id' => (int) $asset->assigned->id,
                'username' => e($asset->assigned->username),
                'name' => e($asset->assigned->getFullNameAttribute()),
                'first_name'=> e($asset->assigned->first_name),
                'last_name'=> ($asset->assigned->last_name) ? e($asset->assigned->last_name) : null,
                'employee_number' =>  ($asset->assigned->employee_num) ? e($asset->assigned->employee_num) : null,
                'type' => 'user',
            ] : null;
        }

        return $asset->assigned ? [
            'id' => $asset->assigned->id,
            'name' => e($asset->assigned->display_name),
            'type' => $asset->assignedType()
        ] : null;
    }


}
