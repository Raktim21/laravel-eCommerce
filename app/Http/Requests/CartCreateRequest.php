<?php

namespace App\Http\Requests;

use App\Models\AttributeVariant;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCombination;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartCreateRequest extends FormRequest
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
            'quantity'               => 'required|integer|min:1',
            'product_combination_id' => ['required','exists:product_combinations,id',
                                        function($attr,$val,$fail) {
                                            $inventory = Inventory::where('product_combination_id', $val)
                                                ->where('stock_quantity','>=',$this->input('quantity'))->first();

                                            $combo = ProductCombination::find($val);

                                            if(is_null($combo) || $combo->is_active == 0) {
                                                $fail('Selected product combination is currently unavailable.');
                                            }

                                            if(is_null($inventory)) {
                                                $fail(__('Selected product combination is out of stock.'));
                                            }
                                        }],
            'user_unique_id'       => 'sometimes|exists:customer_carts,guest_session_id',
        ];
    }

    public function messages()
    {
        return [
            'quantity.required'                 => __('Quantity is required'),
            'quantity.integer'                  => __('Quantity must be integer'),
            'quantity.min'                      => __('Quantity must be greater than 0'),
            'product_combination_id.required'   => __('Please select a valid product combination.'),
            'product_combination_id.exists'     => __('Please select a valid combination'),
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
