<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegistrationRequest extends FormRequest
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
            'name'                  => 'required|string',
            'username'              => 'required|string|email|unique:users,username',
            'phone'                 => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:11|max:11|unique:users',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
            'avatar'                => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gender'                => 'required|exists:user_sexes,id', // 1: male, 2: female
        ];
    }


    public function messages()
    {
        return [

            'name.required'                  => 'Name is required',
            'email.required'                 => 'Email is required',
            'email.email'                    => 'Email is invalid',
            'email.unique'                   => 'You already have an account',
            'phone.required'                 => 'Phone number is required',
            'password.required'              => 'Password is required',
            'password.min'                   => 'Password must be at least 6 characters',
            'password.confirmed'             => 'Password confirmation does not match',
            'password_confirmation.required' => 'Password confirmation is required',
            'avatar.required'                => 'Avatar is required',
            'avatar.image'                   => 'Avatar must be an image',
            'avatar.mimes'                   => 'Avatar must be a file of type: jpeg, png, jpg, gif, svg',
            'avatar.max'                     => 'Avatar may not be greater than 2048 kilobytes',
            'gender.required'                => 'Please select a gender',
            'gender.in'                      => 'Please select only Male and Female'
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
