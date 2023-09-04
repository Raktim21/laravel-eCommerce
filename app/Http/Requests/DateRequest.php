<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DateRequest extends FormRequest
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
            'start_date' => 'sometimes|required|date|date_format:Y-m-d|before:end_date|before:today',
            'end_date'   => 'sometimes|required|date|date_format:Y-m-d|after:start_date',
            'year'       => 'sometimes|required|date_format:Y|before_or_equal:'.date('Y')
        ];
    }


    public function messages()
    {
        return [
            'start_date.required'    => __('Please provide a valid start date'),
            'start_date.date_format' => __('Please provide a valid start date'),
            'start_date.before'      => __('Start date must be greater than end date'),
            'end_date.required'      => __('Please provide a valid end date'),
            'end_date.date_format'   => __('Please provide a valid end date'),
            'end_date.after'         => __('End date must be less than start date'),
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
