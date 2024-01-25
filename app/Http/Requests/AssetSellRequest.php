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
            'assigned_contract'        => 'required_without_all:assigned_user',
        ];

        return $rules;
    }
}
