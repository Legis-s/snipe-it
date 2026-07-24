<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RecognizePurchaseInvoiceRequest extends Request
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $applicationLimitKb = (int) ceil(
            Helper::parse_size(Helper::file_upload_max_size()) / 1024
        );

        return [
            'invoice_file' => [
                'required',
                'file',
                'mimes:pdf,png,jpg,jpeg,webp',
                'max:'.min($applicationLimitKb, 5120),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first('invoice_file'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
