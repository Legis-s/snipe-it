<?php
namespace App\Http\Transformers;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use App\Helpers\Helper;

class PurchasesTransformer
{

    public function transformPurchases (Collection $purchases, $total)
    {
        $array = array();
        foreach ($purchases as $purchase) {
            $array[] = self::transformPurchase($purchase);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformPurchase (Purchase $purchase, $full = false)
    {

        $consumables_count = null;
        $check_count = 0;
        if ($purchase->consumables_json) {
            $consumables = json_decode($purchase->consumables_json, true);
            $consumables_count = count($consumables);
        }
        $array = [
            'id' => (int)$purchase->id,
            'invoice_number' => ($purchase->invoice_number) ? e($purchase->invoice_number) : null,
            'invoice_file' => ($purchase->getInvoiceFile()) ? $purchase->getInvoiceFile() : null,
            'bitrix_id' => ($purchase->bitrix_id) ? e($purchase->bitrix_id) : null,
            'final_price' => ($purchase->final_price) ? e($purchase->final_price) : null,
            'delivery_cost' => ($purchase->delivery_cost) ? e($purchase->delivery_cost) : null,
            'status' => ($purchase->status) ? e($purchase->status) : null,
            'currency' => ($purchase->currency) ? e($purchase->currency) : null,
            'supplier' => ($purchase->supplier) ? [
                'id' => (int)$purchase->supplier->id,
                'name' => e($purchase->supplier->name)
            ] : null,
            'legal_person' => ($purchase->legal_person) ? e($purchase->legal_person->name) : null,
            'invoice_type' => ($purchase->invoice_type) ? e($purchase->invoice_type->name) : null,

            'assets_count' => (int)$purchase->assets_count,
            'consumables_count_real' => (int)$purchase->consumables_count,
            'assets_count_ok' => (int)$purchase->assets_count_ok,
            'consumables_count' => (int)$consumables_count,
            'consumables_check_count' => (int)$check_count,
            'comment' => ($purchase->comment) ? e($purchase->comment) : null,
            'user' => ($purchase->user) ? (new UsersTransformer)->transformUser($purchase->user) : null,
            'created_at' => Helper::getFormattedDateObject($purchase->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($purchase->updated_at, 'datetime'),
            'bitrix_task_id' => (int)$purchase->bitrix_task_id,
        ];

        $permissions_array['available_actions'] = [
            'clone' => (Gate::allows('create', Purchase::class) && ($purchase->deleted_at == '')),
            'delete' => (Gate::allows('superadmin') && ($purchase->status ==  $purchase::REJECTED)),
        ];
        if ($full) {
            $array += ['consumables_json' => ($purchase->consumables_json) ? $consumables : null];
        }
        $array += $permissions_array;

        return $array;
    }

    public function transformPurchasesDatatable($purchases) {
        return (new DatatablesTransformer)->transformDatatables($purchases);
    }

}
