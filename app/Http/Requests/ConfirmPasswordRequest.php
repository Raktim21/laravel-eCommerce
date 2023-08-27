<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConfirmPasswordRequest extends FormRequest
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
            'code' => 'required|string|exists:users,password_reset_code',
            'token' => ['required','string',
                function($attr, $val, $fail) {
                    $user = User::where('password_reset_token', $val)
                        ->where('password_reset_code',$this->input('code'))->first();

                    if(is_null($user))
                    {
                        $fail('The selected token is invalid.');
                    }
                }],
            'password'          => 'required|min:6|max:10',
            'confirm_password'  => 'required|same:password',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'  => false,
            'error'  => $validator->errors()->first(),
        ], 422));
    }
}
