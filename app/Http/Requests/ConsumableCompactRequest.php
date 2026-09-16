<?php

namespace App\Http\Requests;

use App\Models\Consumable;

class ConsumableCompactRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', Consumable::class)
            && $this->user()->can('delete', Consumable::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'id_array' => 'required|array|min:1|max:100',
            'id_array.*' => 'required|integer|min:1|distinct|not_in:'.$this->route('id'),
        ];
    }
}
