<?php

namespace App\Http\Requests;

use App\Models\GalleryHasImage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AvatarUpdateRequest extends FormRequest
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
            'avatar'    => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            'avatar.required' => __('Avatar is required'),
            'avatar.image'    => __('Unsupported file type for avatar'),
            'avatar.mimes'    => __('Unsupported file type for avatar'),
            'avatar.max'      => __('Avatar must be less than 2MB'),
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
