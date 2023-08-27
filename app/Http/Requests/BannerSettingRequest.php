<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BannerSettingRequest extends FormRequest
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
            'title'       => 'sometimes|nullable|string',
            'subtitle'    => 'sometimes|nullable|string',
            'button_text' => 'sometimes|nullable|string',
            'button_url'  => 'sometimes|nullable|string',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

    }


    public function messages()
    {
        return [
            'title.string'       => __('Invalide title'),
            'subtitle.string'    => __('Invalide subtitle'),
            'button_text.string' => __('Invalide button text'),
            'button_url.string'  => __('Invalide button url'),
            'image.image'        => __('Invalide banner image'),
            'image.mimes'        => __('Invalide banner image'),
            'image.max'          => __('Banner image must be less than 2MB'),
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
