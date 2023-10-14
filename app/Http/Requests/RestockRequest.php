<?php

namespace App\Http\Requests;

use App\Models\AttributeVariant;
use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RestockRequest extends FormRequest
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
            'product_id' => ['required','exists:products,id',
                                function ($attr, $val, $fail) {
                                    $stock = Product::whereHas('inventories', function ($q) {
                                        return $q->whereNot('stock_quantity', 0);
                                    })->where('id',$val)->first();

                                    if($stock)
                                    {
                                        $fail('Selected product is already in stock.');
                                    }
                                }],
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => __('Please select a product'),
            'product_id.exists'   => __('The selected product is invalid'),
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
