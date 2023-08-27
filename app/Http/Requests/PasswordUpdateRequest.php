<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordUpdateRequest extends FormRequest
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
            'old_password'          => 'required|string',
            'password'              => 'required|string|confirmed|different:old_password|min:6',
            'password_confirmation' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'old_password.required'              => __('Please provide your old password'),
            'password.required'                  => __('Please provide your new password'),
            'password.confirmed'                 => __('Password and confirm password does not match'),
            'password.min'                       => __('Password must have at least 6 characters'),
            'password.string'                    => __('Please provide a valid password'),
            'password.different'                 => __('Please provide a new password'),
            'password_confirmation.required'     => __('Please provide your confirm password'),
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
