<?php

namespace App\Http\Requests;

class ConsumableCheckoutRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('checkout', \App\Models\Consumable::class);
    }

    /**
     * Normalize legacy user-only checkout submissions before validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if (! $this->filled('checkout_to_type')) {
            $this->merge(['checkout_to_type' => 'user']);
        }
        if (! $this->filled('assigned_user') && $this->filled('assigned_to')) {
            $this->merge(['assigned_user' => $this->input('assigned_to')]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'assigned_user' => 'nullable|integer|required_without_all:assigned_asset,assigned_location,assigned_deal|exists_undeleted:users,id',
            'assigned_asset' => 'nullable|integer|required_without_all:assigned_user,assigned_location,assigned_deal|exists_undeleted:assets,id',
            'assigned_location' => 'nullable|integer|required_without_all:assigned_user,assigned_asset,assigned_deal|exists_undeleted:locations,id',
            'assigned_deal' => 'nullable|integer|required_without_all:assigned_user,assigned_asset,assigned_location|exists_undeleted:deals,id',
            'status_id' => 'nullable|exists:status_labels,id,deployable,1',
            'checkout_qty' => 'nullable|integer|min:1|max:2147483647',
            'checkout_to_type' => 'required|in:asset,location,user,deal',
        ];

        return $rules;
    }
}
