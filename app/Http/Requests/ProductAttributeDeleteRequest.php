<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCombination;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductAttributeDeleteRequest extends FormRequest
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
            'product_id'            => ['required',
                                        function($attr, $val, $fail) {
                                            $product = Product::find($val);

                                            if(is_null($product)) {
                                                $fail('Selected product is invalid.');
                                            }
                                            else {
                                                if($product->inventories()->withTrashed()->count() != 0)
                                                {
                                                    $fail('This product has inventories.');
                                                }
                                                if($product->productAttributes()->count() == 1)
                                                {
                                                    $fail('Product must have at least one attribute.');
                                                }
                                            }
                                        }],
            'attribute_to_delete'   => ['required',
                                        function($attr, $val, $fail) {
                                            $valid = ProductAttribute::where('product_id', $this->input('product_id'))
                                                ->where('id', $val)->first();

                                            if(is_null($valid)) {
                                                $fail('Selected product attribute is invalid.');
                                            }
                                        }],
            'combinations.*.is_default'     => 'required|in:0,1',
            'combinations'          => ['required','array',
                                        function($attr, $val, $fail) {
                                            $isDefaultCount = 0;
                                            foreach ($val as $combination) {
                                                if(!isset($combination['is_default']))
                                                {
                                                    $fail('Default field is required.');
                                                }
                                                else if ($combination['is_default'] == 1) {
                                                    $isDefaultCount++;
                                                }
                                            }
                                            if ($isDefaultCount !== 1) {
                                                $fail('There must be exactly one default combination.');
                                            }


                                        }],
            'combinations.*.id'     => ['required','distinct',
                                        function($attr, $val, $fail) {
                                            $valid = ProductCombination::where('product_id', $this->input('product_id'))
                                                ->where('id',$val);

                                            if(is_null($valid)) {
                                                $fail('Selected product combinations are invalid.');
                                            }
                                        }],
            'combinations.*.selling_price'  => 'required|numeric',
            'combinations.*.cost_price'     => 'required|numeric',
            'combinations.*.weight'         => 'required|numeric|lte:5',
            'combinations.*.quantity'       => 'nullable|numeric|min:1',

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
