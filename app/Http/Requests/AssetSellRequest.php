<?php

namespace App\Http\Requests;

class AssetSellRequest extends Request
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
            'assigned_user'         => 'required_without_all:assigned_contract',
            'assigned_contract'        => 'required_without_all:assigned_user',
            'checkout_to_type_s'      => 'required|in:contract,user',
//            'contract_id'      => 'required|in:contract',
        ];

        return $rules;
    }
}
