<?php

namespace App\Http\Requests;

class ConsumableCheckoutRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'assigned_user'         => 'required_without_all:assigned_asset,assigned_location,assigned_deal',
            'assigned_asset'        => 'required_without_all:assigned_user,assigned_location,assigned_deal',
            'assigned_location'     => 'required_without_all:assigned_user,assigned_asset,assigned_deal',
            'assigned_deal'     => 'required_without_all:assigned_user,assigned_asset,assigned_location',
            'status_id'             => 'exists:status_labels,id,deployable,1',
            'checkout_to_type'      => 'required|in:asset,location,user,deal',
        ];

        return $rules;
    }
}
