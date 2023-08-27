<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryBulkDeleteRequest extends FormRequest
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
                function($val, $fail) {
                    $catIds = is_array($val) ? $val : [$val];

                    $query = ProductCategory::whereIn('id', $catIds)
                        ->where(function ($query) {
                            $query->whereDoesntHave('subCategories')
                                ->whereDoesntHave('products');
                        })->count();

                    if($query != count($catIds))
                    {
                        $fail(__('One or more selected categories can not be deleted.'));
                    }
                }
            ]
        ];
    }


    public function messages()
    {
        return [
            'ids.array'    => __('Please select atleast one category.'),
            'ids.required' => __('Please select atleast one category.'),
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
