<?php

namespace App\Http\Requests;

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
        return [
            'dashboard_language_id' => 'sometimes|exists,dashboard_languages,id',
            'currency_id' => 'sometimes|exists:currencies,id',
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:100',
            'phone' => 'sometimes|string|max:30',
            'address' => 'sometimes|string',
            'about' => 'sometimes|string|max:500',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'dark_logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'theme_color' => 'sometimes|string',
            'text_color' => 'sometimes|string',
            'badge_background_color' => 'sometimes|string',
            'badge_text_color' => 'sometimes|string',
            'button_color' => 'sometimes|string',
            'button_text_color' => 'sometimes|string',
            'price_color' => 'sometimes|string',
            'discount_price_color' => 'sometimes|string',
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
            'theme_color.sometimes'            => __('Please provide your theme color'),
            'theme_color.string'              => __('Theme color must be a string'),
            'text_color.sometimes'             => __('Please provide your text color'),
            'text_color.string'               => __('Text color must be a string'),
            'badge_background_color.sometimes' => __('Please provide your badge background color'),
            'badge_background_color.string'   => __('Badge background color must be a string'),
            'badge_text_color.sometimes'       => __('Please provide your badge text color'),
            'badge_text_color.string'         => __('Badge text color must be a string'),
            'button_color.sometimes'           => __('Please provide your button color'),
            'button_color.string'             => __('Button color must be a string'),
            'button_text_color.sometimes'      => __('Please provide your button text color'),
            'button_text_color.string'        => __('Button text color must be a string'),
            'price_color.sometimes'            => __('Please provide your price color'),
            'price_color.string'              => __('Price color must be a string'),
            'discount_price_color.sometimes'   => __('Please provide your discount price color'),
            'discount_price_color.string'     => __('Discount price color must be a string'),

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
