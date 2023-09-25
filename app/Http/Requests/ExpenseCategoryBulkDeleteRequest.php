<?php

namespace App\Http\Requests;

use App\Models\ExpenseCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExpenseCategoryBulkDeleteRequest extends FormRequest
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
                    $catIds = is_array($val) ? $val : [$val];

                    $query = ExpenseCategory::whereIn('id', $catIds)
                        ->where(function ($query) {
                            $query->whereDoesntHave('expences');
                        })->count();

                    if($query != count($catIds))
                    {
                        $fail(__('One or more selected expense categories can not be deleted'));
                    }
                }
            ]
        ];
    }


    public function messages()
    {
        return [
            'ids.required' => __('Please select at least one expense category'),
            'ids.array'    => __('Please select at least one expense category'),
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
