<?php

namespace App\Http\Requests;

class ConsumableReceiptRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()->can('review');
    }

    protected $rules = [
        'purchase_id' => 'required|integer|exists:purchases,id',
        'quantity' => 'required|integer|min:1|max:2147483647',
        'purchase_cost' => 'required|numeric|min:0|max:99999999999999999.99',
        'row_id' => 'nullable|integer',
    ];
}
