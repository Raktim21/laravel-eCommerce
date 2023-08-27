<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactBulkDeleteRequest extends FormRequest
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
            'ids'   => 'required|array',
            'ids.*' => 'required|exists:contact_forms,id',
        ];
    }

    public function messages()
    {
        return [
            'ids.required'    => __('Please select at least one contact.'),
            'ids.array'       => __('Please select at least one contact.'),
            'ids.*.exists'    => __('Please select valid contact.'),
            'ids.*.required'  => __('Please select valid contact.'),

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
