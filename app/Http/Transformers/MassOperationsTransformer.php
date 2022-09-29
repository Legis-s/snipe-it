<?php
namespace App\Http\Transformers;

use App\Models\MassOperation;
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
        $type = '';
        if ($mo->assigned_type == "App\Models\User") {
            $type = 'users';
        } else if ($mo->assigned_type == "App\Models\Location") {
            $type = 'locations';
        } else if ($mo->assigned_type == "App\Models\Contract") {
            $type = 'contracts';
        }

        $purpose_name = "<li class='fa fa-user text-black'></li> " .$mo->last_name . ' ' . $mo->first_name;
        if ($type == 'contracts') {
            $purpose_name = "<li class='fa fa-file-word-o text-black'></li> " . $mo->contract_name;
        } else if ($type == 'locations') {
            $purpose_name = "<li class='fa fa-map-marker text-black'></li> " . $mo->location_name;
        }


        if ($mo->operation_type == 'sell') {
            $operation_type = "<li class='fa fa-rub text-black'></li> Продажа";
        } else if ($mo->operation_type == 'checkout') {
            $operation_type = "<li class='fa fa-hand-o-right text-black'></li> Выдача";
        } else if ($mo->operation_type == 'checkin') {
            $operation_type = "<li class='fa fa-hand-o-left text-black'></li> Возврат";
        }

        $array = [
            'id' => (int) $mo->id,
            'operation_type' => $operation_type,
            'name' => e($mo->name),
            'assets_count' => e($mo->assets_count),
            'date' => e($mo->created_at->format('m.d.Y')),
            'purpose' => "<a href='/" . $type . "/" . e($mo->assigned_to) . "' data-tooltip='true' title='" . substr($type, 0, -1) . "'>" . $purpose_name  . '</a>',
            'notes' => e($mo->note)


//            'company' => ($license->company) ? ['id' => (int) $license->company->id,'name'=> e($license->company->name)] : null,
//            'manufacturer' =>  ($license->manufacturer) ? ['id' => (int) $license->manufacturer->id,'name'=> e($license->manufacturer->name)] : null,
//            'product_key' => (Gate::allows('viewKeys', License::class)) ? e($license->serial) : '------------',
//            'order_number' => e($license->order_number),
//            'purchase_order' => e($license->purchase_order),
//            'purchase_date' => Helper::getFormattedDateObject($license->purchase_date, 'date'),
//            'purchase_cost' => e($license->purchase_cost),
//            'notes' => e($license->notes),
//            'expiration_date' => Helper::getFormattedDateObject($license->expiration_date, 'date'),
//            'seats' => (int) $license->seats,
//            'free_seats_count' => (int) $license->free_seats_count,
//            'license_name' =>  e($license->license_name),
//            'license_email' => e($license->license_email),
//            'maintained' => ($license->maintained == 1) ? true : false,
//            'supplier' =>  ($license->supplier) ? ['id' => (int)  $license->supplier->id,'name'=> e($license->supplier->name)] : null,
//            'category' =>  ($license->category) ? ['id' => (int)  $license->category->id,'name'=> e($license->category->name)] : null,
//            'created_at' => Helper::getFormattedDateObject($license->created_at, 'datetime'),
//            'updated_at' => Helper::getFormattedDateObject($license->updated_at, 'datetime'),
//            'user_can_checkout' => (bool) ($license->free_seats_count > 0),
        ];
//
//        $permissions_array['available_actions'] = [
//            'checkout' => Gate::allows('checkout', License::class) ? true : false,
//            'checkin' => Gate::allows('checkin', License::class) ? true : false,
//            'clone' => Gate::allows('create', License::class) ? true : false,
//            'update' => Gate::allows('update', License::class) ? true : false,
//            'delete' => Gate::allows('delete', License::class) ? true : false,
//        ];

//        $array += $permissions_array;

        return $array;
    }
//
//    public function transformAssetsDatatable($licenses) {
//        return (new DatatablesTransformer)->transformDatatables($licenses);
//    }



}
