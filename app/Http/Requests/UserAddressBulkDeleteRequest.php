<?php

namespace App\Http\Requests;

use App\Models\UserAddress;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserAddressBulkDeleteRequest extends FormRequest
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
            'ids' => ['required','array',
                function($attr, $val, $fail) {
                    $addressIds = is_array($val) ? $val : [$val];

                    $query = UserAddress::whereIn('id', $addressIds)
                        ->where(function ($query) {
                            $query->where('is_default',0);
                        })->count();

                    if($query != count($addressIds))
                    {
                        $fail('You have selected one or more featured addresses.');
                    }
                }
            ]
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
