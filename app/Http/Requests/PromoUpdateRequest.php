<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PromoUpdateRequest extends FormRequest
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
        $rules = [
            'title'         => 'required|string|max:100',
            'code'          => 'required|string|unique:promo_codes,code,'.$this->route('id'),
            'discount'      => 'required|numeric',
            'start_date'    => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
            'expiration'    => 'sometimes|in:1',
            'end_date'      => 'nullable|sometimes|date_format:Y-m-d H:i:s|after:start_date',
            'is_percent'    => 'required|in:0,1',
            'max_usage'     => 'required|integer',
            'max_num_users' => 'sometimes|integer|min:1',
        ];

        if ($this->input('is_percent') == 1)
        {
            $rules['discount'] = 'lte:100';
        }

        if($this->input('expiration') == 1)
        {
            $rules['end_date'] = 'required';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }
}
