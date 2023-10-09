<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
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
            'name'                 => 'required|string|max:98',
            'description'          => 'required|string|max:1000',
            'short_description'    => 'nullable|string|max:500',
            'thumbnail_image'      => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'featured_image'       => 'nullable|sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id'          => ['required',
                                        Rule::exists('product_categories','id')->where('status', 1),
                                      ],
            'category_sub_id'      => ['nullable',
                                        Rule::exists('product_categories_sub','id')
                                            ->where('category_id', $this->input('category_id'))
                                       ],
            'display_price'        => 'required|numeric|max:999999',
            'previous_display_price' => ['nullable','numeric','max:999999',
                                        function ($attr, $val, $fail) {
                                            if ($val >= $this->input('display_price')) {
                                                $fail('Previous display price must be less than display price.');
                                            }
                                        }],
            'brand_id'             => 'nullable|exists:product_brands,id',
            'is_featured'          => 'sometimes|in:0,1',
            'cost_price'           => 'required|numeric|max:999999',
            'multiple_image'       => 'nullable|array|min:1',
            'multiple_image.*'     => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'weight'               => 'required|numeric|max:5',
            'meta_title'           => 'sometimes|nullable|string|max:100',
            'meta_description'     => 'sometimes|nullable|string|max:100',
            'meta_keywords'        => 'sometimes|nullable|string|max:100',
            'attribute_list'       => 'nullable|string',
        ];

        return $rules;
    }



    public function messages()
    {
        return [
            'name.required'            => __('Please enter a name for the product'),
            'name.string'              => __('Please enter a valid name for the product'),
            'name.max'                 => __('The name is too long'),
            'description.required'     => __('Please enter a description for the product'),
            'description.string'       => __('Please enter a valid description for the product'),
            'description.max'          => __('The description is too long'),
            'short_description.string' => __('Please enter a valid short description for the product'),
            'short_description.max'    => __('The short description is too long'),
            'category_id.required'     => __('Please select a category'),
            'category_id.exists'       => __('The selected category is invalid'),
            'sub_category_id.exists'   => __('The selected sub category is invalid'),
            'brand_id.exists'          => __('The selected brand is invalid'),
            'cost.required'            => __('Please enter a cost for the product'),
            'cost.numeric'             => __('Please enter a valid cost for the product'),
            'cost.max'                 => __('The cost is too long'),
            'price.required'           => __('Please enter a price for the product'),
            'price.numeric'            => __('Please enter a valid price for the product'),
            'price.max'                => __('The price is too long'),
            'multiple_image.array'     => __('Please select at least one image for the product'),
            'multiple_image.min'       => __('Please select at least one image for the product'),
            'multiple_image.*.image'   => __('Please select a valid image for the product'),
            'multiple_image.*.mimes'   => __('Please select a valid image for the product'),
            'weight.required'          => __('Please enter a weight for the product'),
            'weight.numeric'           => __('Please enter a valid weight for the product'),
            'weight.max'               => __('The weight is too long'),
            'background_image.string'  => __('Please enter a valid background image for the product'),
            'background_image.max'     => __('The background image is too long'),
            'featured_image.image'     => __('Please select a valid image for the product'),
            'featured_image.mimes'     => __('Please select a valid image for the product'),
            'featured_image.max'       => __('The featured image is too large'),
            'image_url.image'          => __('Please select a valid image for the product'),
            'image_url.mimes'          => __('Please select a valid image for the product'),
            'image_url.max'            => __('The image is too large'),
            'meta_title.string'        => __('Please enter a valid meta title for the product'),
            'meta_title.max'           => __('The meta title is too long'),
            'meta_description.string'  => __('Please enter a valid meta description for the product'),
            'meta_description.max'     => __('The meta description is too long'),
            'meta_keywords.string'     => __('Please enter a valid meta keywords for the product'),
            'meta_keywords.max'        => __('The meta keywords is too long'),
            'attribute_list.string'    => __('Please enter a valid attribute list for the product'),
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
