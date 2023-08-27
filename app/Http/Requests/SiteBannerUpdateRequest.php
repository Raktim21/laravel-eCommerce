<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SiteBannerUpdateRequest extends FormRequest
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
            'flash_sale_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'new_arrival_image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            'new_arrival_image2'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount_product_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'popular_product_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            'popular_product_image2'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'newsletter_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'featured_banner_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'all_product_side_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'featured_product_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors()->all()
        ], 422));
    }
}
