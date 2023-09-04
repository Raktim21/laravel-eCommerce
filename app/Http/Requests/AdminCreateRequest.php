<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminCreateRequest extends FormRequest
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
            'name'                  => 'required|string|max:98',
            'username'              => 'required|string|email|max:98|unique:users,username',
            'phone'                 => ['required','string',
                                        'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/','unique:users,phone'],
            'avatar'                => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gender'                => 'required|exists:user_sexes,id', // 1: male, 2: female
            'role'                  => 'required|exists:roles,id',
            'shop_branch_id'        => 'required|exists:shop_branches,id'
        ];
    }




    public function messages()
    {
        return [
            'name.required'      => __('Please enter a name'),
            'name.string'        => __('Please enter a valid name'),
            'email.required'     => __('Please enter an email'),
            'email.string'       => __('Please enter a valid email'),
            'email.email'        => __('Please enter a valid email'),
            'email.unique'       => __('This email is already registered'),
            'password.required'  => __('Please enter a password'),
            'password.string'    => __('Please enter a valid password'),
            'password.confirmed' => __('Please enter the same password'),
            'password.max'       => __('Please enter a valid password'),
            'phone.required'     => __('Please enter a phone number'),
            'phone.string'       => __('Please enter a valid phone number'),
            'avatar.required'    => __('Please select an avatar'),
            'avatar.image'       => __('Please select an image'),
            'avatar.mimes'       => __('Please select an image'),
            'role.required'      => __('Please select a role'),
            'role.exists'        => __('Please select a valid role'),
            'gender.required'    => __('Please select a gender'),
            'gender.in'          => __('Please select a valid gender'),
            'phone.regex'        => __('Please enter a valid phone number'),
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
