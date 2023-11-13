<?php

namespace App\Http\Requests;

use App\Models\GalleryHasImage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BrandRequest extends FormRequest
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
            'name'  => 'required|max:50|unique:product_brands,name',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_id'  => ['sometimes','integer',
                            function($attr, $val, $fail) {
                                $img = GalleryHasImage::find($val);

                                if (!$img)
                                {
                                    $fail('No image found.');
                                }

                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                    $fail('You cannot select an image from private folder.');
                                }
                            }]
        ];
    }


    public function messages()
    {
        return [
            'name.required'  => __('Please enter a name'),
            'image.image'    => __('Invalid brand image'),
            'image.mimes'    => __('Invalid brand image'),
            'image.max'      => __('Brand image must be less than 2MB'),
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
