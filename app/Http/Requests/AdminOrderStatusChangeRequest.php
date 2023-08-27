<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class AdminOrderStatusChangeRequest extends FormRequest
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
            'status'            => 'required|exists:order_statuses,id|not_in:4',
            'shop_branch_id'    => 'required|exists:shop_branches,id',
            'merchant_remarks'  => 'sometimes|string|max:490'
        ];
    }


    public function messages()
    {
        return [
            'status.required' => __('Please provide the order status'),
            'status.exists'   => __('Order status is invalid'),
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
