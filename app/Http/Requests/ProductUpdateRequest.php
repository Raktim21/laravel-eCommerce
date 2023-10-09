<?php

namespace App\Http\Requests;

use App\Models\ProductSubCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductUpdateRequest extends FormRequest
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
            'name'                 => 'sometimes|string|max:100',
            'description'          => 'string',
            'short_description'    => 'string|max:500',
            'thumbnail_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id'          => 'required|exists:product_categories,id',
            'category_sub_id'      => ['sometimes',
                                        function($attr, $val, $fail) {
                                            $exist = ProductSubCategory::where('id', $val)
                                                ->where('category_id', $this->input('category_id'))->first();

                                            if (!$exist)
                                            {
                                                $fail('Selected sub category does not belong to the given category.');
                                            }
                                        }],
            'brand_id'             => 'nullable|sometimes|exists:product_brands,id',
            'is_featured'          => 'sometimes|in:0,1',
            'multiple_image'       => 'nullable|array|min:1|max:5',
            'multiple_image.*'     => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'status'               => 'required|in:0,1',
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
