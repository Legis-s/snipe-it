<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\InvoiceType;
use App\Models\Location;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class InvoiceTypesTransformer
{
    public function transformInvoiceTypes(Collection $invoicetypes, $total)
    {
        $array = [];
        foreach ($invoicetypes as $invoicetype) {
            $array[] = self::transformInvoiceType($invoicetype);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformInvoiceType(InvoiceType $invoicetype = null)
    {
        if ($invoicetype) {

            $array = [
                'id' => (int) $invoicetype->id,
                'name' => e($invoicetype->name),
                'created_at' => Helper::getFormattedDateObject($invoicetype->created_at, 'datetime'),
                'bitrix_id' => ($invoicetype->bitrix_id) ? (int)$invoicetype->bitrix_id : null,
                'active' => ($invoicetype->active) ? e($invoicetype->active) : null,
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Location::class) ? true : false,
            ];

            $array += $permissions_array;

            return $array;
        }
    }

}
