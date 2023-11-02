<?php

namespace App\Http\Requests;

use App\Models\GalleryHasImage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryUpdateRequest extends FormRequest
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
            'name'      => 'required|unique:product_categories,name,'.$this->route('id'),
            'image'     => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
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
            'name.required'  => __('Category name is required'),
            'name.unique'    => __('Category name must be unique'),
            'image.required' => __('Please provide a category image'),
            'image.mimes'    => __('Invalid category image.'),
            'image.max'      => __('Category image must be less than 2MB.'),
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
