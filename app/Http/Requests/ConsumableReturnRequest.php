<?php

namespace App\Http\Requests;

use App\Models\Consumable;

class ConsumableReturnRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()->can('checkout', Consumable::class);
    }

    protected $rules = [
        'quantity' => 'required|integer|min:1|max:2147483647',
    ];
}
