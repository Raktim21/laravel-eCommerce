<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExpenseRequest extends FormRequest
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
            'title'                => 'required|string|max:255',
            'expense_category_id'  => 'required|exists:expense_categories,id',
            'amount'               => 'required|numeric',
            'event_date'                 => 'required|date',
            'description'          => 'required|string|max:500',
        ];
    }



    public function messages()
    {
        return [
            'title.required'               => __('Please provide a title'),
            'title.string'                 => __('Please provide a valid title'),
            'title.max'                    => __('Title must be less than 255 characters'),
            'expense_category_id.required' => __('Please select a expense category'),
            'expense_category_id.numeric'  => __('Please select a valid expense category'),
            'expense_category_id.exists'   => __('Expense category must be exists'),
            'amount.required'              => __('Amount is required'),
            'amount.numeric'               => __('Amount must be numeric'),
            'amount.max_float'             => __('Amount must be less than 9999999.99'),
            'event_date.required'          => __('Date is required'),
            'event_date.date'              => __('Date must be date'),
            'description.required'         => __('Description is required'),
            'description.string'           => __('Description must be string'),
            'description.max'              => __('Description must be less than 255 characters'),
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
