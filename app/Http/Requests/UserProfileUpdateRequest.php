<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserProfileUpdateRequest extends FormRequest
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
        $id = $this->route('id') ?? auth()->user()->id;

        return [
            'name'                  => 'required|string',
            'username'              => 'required|string|email|unique:users,username,' . $id,
            'phone'                 =>  [
                                            'required',
                                            'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                                            'string',
                                            'unique:users,phone,' . $id
                                        ],
            'gender'                => 'required|exists:user_sexes,id',
            'role'                  => 'sometimes|exists:roles,id',
            'shop_branch_id'        => 'sometimes|exists:shop_branches,id'
        ];
    }

    public function messages()
    {
        return [
            'name.required'             => __('Please provide a name'),
            'name.string'               => __('Please provide a valid name'),
            'username.required'         => __('Please provide your email'),
            'username.string'           => __('Please provide a valid email'),
            'username.email'            => __('Please provide a valid email'),
            'username.unique'           => __('This email is already registered'),
            'phone.required'            => __('Please provide your phone number'),
            'phone.string'              => __('Please provide a valid phone number'),
            'phone.unique'              => __('This phone number is already registered'),
            'phone.regex'               => __('Please provide a valid phone number'),
            'gender.required'           => __('Please select a gender'),
            'gender.exists'             => __('Please select a valid gender'),
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
