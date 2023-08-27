<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BillingStoreRequest extends FormRequest
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
        $rules = [
            'user_id'                               => 'nullable|exists:users,id',
            'discount_amount'                       => 'nullable|numeric|lte:100',
            'product_combinations.*.quantity'       => 'required|integer|min:1',
            'product_combinations.*.combination_id' => 'required|exists:product_combinations,id|distinct',
            'product_combinations'                  => ['required','array',
                                                        function($attr, $val, $fail) {
                                                            foreach ($val as $item) {
                                                                $inventory = Inventory::where('product_combination_id', $item['combination_id'])
                                                                    ->where('shop_branch_id', auth()->guard('admin-api')->user()->shop_branch_id)
                                                                    ->where('stock_quantity','>=',$item['quantity'])->first();

                                                                if(is_null($inventory)) {
                                                                    $fail(__('Selected product combination is out of stock.'));
                                                                }
                                                            }
                                                        }],
            'customer_name'                         => 'nullable|string|max:100',
            'customer_email'                        => 'nullable|email|max:100',
            'customer_phone'                        => ['nullable','regex:/^(?:\+88|88)?(01[3-9]\d{8})$/'],
            'is_follow_up'                          => 'sometimes|in:1'
        ];

        if(is_null($this->input('user_id')))
        {
            $rules['customer_name'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.exists'                                    => __('User not found'),
            'discount.numeric'                                  => __('Discount must be numeric value'),
            'discount.lte'                                      => __('Discount must be less than 100'),
            'product_combinations.required'                     => __('Please select at least one product combination'),
            'product_combinations.array'                        => __('Please select at least one product_combination'),
            'product_combinations.*.combination_id.required'    => __('Please select at least one product_combination'),
            'product_combinations.*.combination_id.exists'      => __('Product combination not found'),
            'product_combinations.*.quantity.integer'           => __('Quantity must be an integer'),
            'product_combinations.*.quantity.min'               => __('Quantity must be greater than 0'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }
}
