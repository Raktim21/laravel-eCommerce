<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class HomepageRequest extends FormRequest
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
            'per_page'        => 'required|integer',
            'search'          => 'sometimes|string',
            'category_id'     => 'sometimes|exists:product_categories,id',
            'sub_category_id' => 'sometimes|exists:product_categories_sub,id',
//            'brands'          => 'sometimes|array',
//            'brands.*'        => 'required|exists:product_brands,id',
            'sort_by'         => 'sometimes|nullable',
            'flash_sale'      => 'sometimes|in:0,1',
            'featured'        => 'sometimes|in:0,1',
            'discount_product'=> 'sometimes|in:0,1',
            'min_price'       => 'required_with:max_price|numeric|min:0',
            'max_price'       => 'required_with:min_price|numeric|gte:min_price'
        ];
    }

    public function messages()
    {
        return [
            'per_page.required'        => __('Please provide a number for product per page'),
            'per_page.integer'         => __('Please provide a valid number for product per page'),
            'search.required'          => __('Please provide something for search'),
            'search.string'            => __('Invalid search parameters'),
            'category_id.exists'       => __('Your selected category is invalid'),
            'sub_category_id.exists'   => __('Your selected sub category is invalid'),
            'brands.array'             => __('Please select at least one brand'),
            'brands.*.exists'          => __('Your selected brand is invalid'),
            'flash_sale.in'            => __('Your selected flash sale is invalid'),
            'featured.in'              => __('Your selected featured is invalid'),
            'discount_product.in'      => __('Your selected discount product is invalid'),
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
