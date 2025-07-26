<?php


namespace App\Http\Transformers;


use App\Helpers\Helper;
use App\Models\ConsumableAssignment;
use Illuminate\Database\Eloquent\Collection;

class ConsumableAssignmentTransformer
{

    public function transformConsumableAssignments(Collection $consumableAssignments, $total)
    {
        $array = array();
        foreach ($consumableAssignments as $consumableAssignment) {
            $array[] = self::transformConsumableAssignment($consumableAssignment);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformConsumableAssignment(ConsumableAssignment $consumableAssignment = null)
    {
        if ($consumableAssignment) {

            $array = [
                'id' => (int)$consumableAssignment->id,
                'name' => $this->transformName($consumableAssignment),
                'consumable' => ($consumableAssignment->consumable) ? (new ConsumablesTransformer())->transformConsumable($consumableAssignment->consumable) : null,
                'quantity' => e($consumableAssignment->quantity),
                'type' => e($consumableAssignment->type),
                'cost' => e($consumableAssignment->cost),
                'contract' => ($consumableAssignment->contract) ? (new ContractsTransformer())->transformContract($consumableAssignment->contract) : null,
                'deal' => ($consumableAssignment->deal) ? (new DealsTransformer())->transformDeal($consumableAssignment->deal) : null,
                'responsibleUser' => ($consumableAssignment->responsibleUser) ? (new UsersTransformer)->transformUser($consumableAssignment->responsibleUser) : null,
                'assigned_to' => $this->transformAssignedTo($consumableAssignment),
                'comment' => ($consumableAssignment->comment) ? e($consumableAssignment->comment) : null,
                'created_at' => Helper::getFormattedDateObject($consumableAssignment->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($consumableAssignment->updated_at, 'datetime'),
                'can_return' => (bool) $consumableAssignment->availableForReturn(),
//                'can_close_documents' => (bool) $consumableAssignment->availableForCloseDocuments(),
                'created_by' => ($consumableAssignment->adminuser) ? [
                    'id' => (int) $consumableAssignment->adminuser->id,
                    'name' => e($consumableAssignment->adminuser->getFullNameAttribute()),
                    'first_name'=> e($consumableAssignment->adminuser->first_name),
                    'last_name'=> e($consumableAssignment->adminuser->last_name)
                ] : null,
            ];


            return $array;
        }
    }

    public function transformAssignedTo($consumableAssignment)
    {
        if ($consumableAssignment->checkedOutToUser()) {
            return $consumableAssignment->assigned ? [
                'id' => (int) $consumableAssignment->assigned->id,
                'username' => e($consumableAssignment->assigned->username),
                'name' => e($consumableAssignment->assigned->getFullNameAttribute()),
                'first_name'=> e($consumableAssignment->assigned->first_name),
                'last_name'=> ($consumableAssignment->assigned->last_name) ? e($consumableAssignment->assigned->last_name) : null,
                'employee_number' =>  ($consumableAssignment->assigned->employee_num) ? e($consumableAssignment->assigned->employee_num) : null,
                'type' => 'user'
            ] : null;
        }
        if ($consumableAssignment->checkedOutToPurchase()) {
            return $consumableAssignment->assigned ? [
                'id' => $consumableAssignment->assigned->id,
                'name' => $consumableAssignment->assigned->invoice_number,
                'type' => $consumableAssignment->assignedType()
            ] : null;
        }
        return $consumableAssignment->assigned ? [
            'id' => $consumableAssignment->assigned->id,
            'name' => $consumableAssignment->assigned->display_name,
            'type' => $consumableAssignment->assignedType()
        ] : null;
    }

    public function transformName($consumableAssignment)
    {
        switch ($consumableAssignment->type){
            case ConsumableAssignment::PURCHASE:
                return "Закупленно по заявке";
            case ConsumableAssignment::ISSUED:
                $str = "";
                switch ($consumableAssignment->assignedType()){
                    case ConsumableAssignment::ASSET:
                        $str=" на актив";
                        break;
                    case ConsumableAssignment::USER:
                        $str=" пользователю";
                        break;
                    case ConsumableAssignment::LOCATION:
                        $str=" на объект";
                        break;
                    case ConsumableAssignment::DEAL:
                        $str=" по сделке";
                        break;
                }
                return "Выдано ".$str;
            case ConsumableAssignment::SOLD:
                $str = "";
                switch ($consumableAssignment->assignedType()){
                    case ConsumableAssignment::ASSET:
                        $str=" на актив";
                        break;
                    case ConsumableAssignment::USER:
                        $str=" пользователю";
                        break;
                    case ConsumableAssignment::LOCATION:
                        $str=" на объект";
                        break;
                    case ConsumableAssignment::CONTRACT:
                        $str=" по договору";

                    case ConsumableAssignment::DEAL:
                        $str=" по сделке";
                        break;
                }
                return "Продано ".$str;
            case ConsumableAssignment::CONVERTED:
                return "Конвертированно из активов ";
            case ConsumableAssignment::COLLECTED:
                return "Cобранно из расходников";
            case ConsumableAssignment::MANUALLY:
                return "Добавленно вручную";

        }
    }

}