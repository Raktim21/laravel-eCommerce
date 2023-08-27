<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCombination;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Sabberworm\CSS\Rule\Rule;

class ProductAttributeStoreRequest extends FormRequest
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
            'product_id'    => 'required|exists:products,id',
            'name'          => ['required','max:100',
                                function($attr,$val,$fail) {
                                    $product_attribute = ProductAttribute::where('product_id',$this->input('product_id'))
                                        ->where('name',$val)->first();

                                    if(!is_null($product_attribute)) {
                                        $fail('Selected attribute already exists for this product.');
                                    }
                                }],
            'values'        => ['required','array','min:1'],
            'values.*'      => 'required|distinct|string|max:100',
            'combinations'  => ['required','array','size:'.$this->getCount($this->input('product_id'), is_array($this->input('values')) ? count($this->input('values')) : 0),
                                    function($attr, $val, $fail) {
                                        $isDefaultCount = 0;
                                        foreach ($val as $combination) {
                                            $nullValueCount = 0;
                                            if ($combination['is_default'] == 1) {
                                                if($combination['inactive'] == 1)
                                                {
                                                    $fail('Default combination can not be deactivated.');
                                                }
                                                $isDefaultCount++;
                                            }
                                            foreach ($combination['values'] as $value) {
                                                if ($value['id'] === null) {
                                                    $nullValueCount++;
                                                }
                                            }

                                            if ($nullValueCount !== 1) {
                                                $fail('There must be exactly one new attribute value in each combination.');
                                            }
                                        }
                                        if ($isDefaultCount !== 1) {
                                            $fail('There must be exactly one default combination.');
                                        }


                                    }],
            'combinations.*.id' => ['nullable','integer',
                                    function($attr, $val, $fail) {
                                        $combo = ProductCombination::withTrashed()->find($val);

                                        if(is_null($combo))
                                        {
                                            $fail('Some of the given product combinations are invalid.');
                                        }
                                    }],
            'combinations.*.selling_price'  => 'required|numeric',
            'combinations.*.cost_price'     => 'required|numeric',
            'combinations.*.weight'         => 'required|numeric|lte:5',
            'combinations.*.is_default'     => 'required|in:0,1',
            'combinations.*.quantity'       => 'nullable|integer|min:0',
            'combinations.*.inactive'       => 'required|in:0,1',
            'combinations.*.values'         => ['required','array'],
            'combinations.*.values.*.id'    => ['nullable',
                                                function($attr, $val, $fail) {
                                                    $exist = ProductAttributeValue::find($val);

                                                    if(is_null($exist) || ($exist->attribute->product_id != $this->input('product_id')))
                                                    {
                                                        $fail('Invalid product combination value provided.');
                                                    }
                                                }],
            'combinations.*.values.*.name'  => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'product_id.required'    => __('Please select a product'),
            'product_id.exists'      => __('Please select a valid product'),
            'name.required'          => __('Please provide the attribute name'),
            'name.max'               => __('The attribute name is too long'),
            'values.required'      => __('Please select at least one variant'),
            'values.*.required'    => __('Please select at least one variant'),
            'values.*.max'         => __('The variant name is too long'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }

    private function getCount($product_id, $no_values)
    {
        $product = Product::find($product_id);
        $count = $no_values;
        foreach ($product->productAttributes as $attribute) {
            $count *= $attribute->attributeValues->count();
        }

        return $count;
    }
}
