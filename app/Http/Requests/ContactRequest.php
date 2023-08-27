<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactRequest extends FormRequest
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
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|string|email',
            'phone'      => 'required|string',
            'message'    => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => __('Please provide your first name'),
            'first_name.string'   => __('Please provide a valide first name'),
            'last_name.required'  => __('Please provide your last name'),
            'last_name.string'    => __('Please provide a valide  last name'),
            'email.required'      => __('Please provide your email'),
            'email.string'        => __('Please provide a valid email'),
            'email.email'         => __('Please provide a valid email'),
            'phone.required'      => __('Please provide your phone number'),
            'phone.string'        => __('Please provide a valid phone number'),
            'message.required'    => __('Please provide your message'),
            'message.string'      => __('Please provide your message'),
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
