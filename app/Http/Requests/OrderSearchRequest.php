<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderSearchRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'customer_name'     => 'sometimes|string',
            'customer_phone'    => ['sometimes','string','regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/'],
            'delivery_status'   => 'sometimes|in:Not Picked Yet,Picked,Delivered,Cancelled',
            'order_status'      => 'sometimes|in:1,2,3,4',
            'order_number'      => 'sometimes|string',
            'tracking_number'   => 'sometimes|string',
            'start_date'        => 'required_with:end_date|date_format:Y-m-d|before:end_date|before:today',
            'end_date'          => 'required_with:start_date|date_format:Y-m-d|after:start_date'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }
}
