<?php

namespace App\Http\Requests;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminBulkDeleteRequest extends FormRequest
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
            'ids.*' => ['required','integer',
                        function($attr,$val,$fail) {
                            $admin = User::find($val);

                            if(is_null($admin))
                            {
                                $fail('Selected admin is invalid.');
                            }

                            else if($admin->hasRole('Super Admin')) {
                                $fail('Super admins cannot be deleted.');
                            }
                        }]
        ];
    }

    public function messages()
    {
        return [
            'ids.required' => __('Please select at least one admin.'),
            'ids.*.exists' => __('Selected admin is invalid.'),
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
