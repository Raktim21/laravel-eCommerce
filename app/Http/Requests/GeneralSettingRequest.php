<?php

namespace App\Http\Requests;

use App\Models\GalleryHasImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GeneralSettingRequest extends FormRequest
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
        $gallery = new GalleryHasImage();

        return [
            'dashboard_language_id' => 'sometimes|exists,dashboard_languages,id',
            'name'                  => 'sometimes|string|max:100',
            'email'                 => 'sometimes|email|max:100',
            'phone'                 => 'sometimes|string|max:30',
            'address'               => 'sometimes|string',
            'about'                 => 'sometimes|string|max:500',
            'logo'                  => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'dark_logo'             => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon'               => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'logo_image_id'         => ['sometimes','integer',
                                        function($attr, $val, $fail) use ($gallery) {
                                            $img = $gallery->find($val);

                                            if (!$img)
                                            {
                                                $fail('No image found.');
                                            }

                                            else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                $fail('You cannot select an image from private folder.');
                                            }
                                        }],
            'dark_logo_image_id'    => ['sometimes','integer',
                                        function($attr, $val, $fail) use ($gallery) {
                                            $img = $gallery->find($val);

                                            if (!$img)
                                            {
                                                $fail('No image found.');
                                            }

                                            else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                $fail('You cannot select an image from private folder.');
                                            }
                                        }],
            'favicon_image_id'       => ['sometimes','integer',
                                        function($attr, $val, $fail) use ($gallery) {
                                            $img = $gallery->find($val);

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
            'name.string'                => __('Please provide a valid website name'),
            'name.max'                   => __('Website name may not be greater than 100 characters'),
            'email.email'                => __('Please provide a valid website email'),
            'phone.string'               => __('Please provide a valid website phone'),
            'address.string'             => __('Please provide a valid website address'),
            'about.string'               => __('Please provide a valid website about'),
            'logo.image'                      => __('Invalid logo'),
            'logo.mimes'                      => __('Logo must be a file of type: jpeg, png, jpg, gif, svg'),
            'logo.max'                        => __('Logo may not be greater than 2048 kilobytes'),
            'favicon.image'                   => __('Invalid favicon'),
            'favicon.mimes'                   => __('Favicon must be a file of type: jpeg, png, jpg, gif, svg'),
            'favicon.max'                     => __('Favicon may not be greater than 2048 kilobytes'),
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
