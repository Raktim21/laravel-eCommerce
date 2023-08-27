<?php

namespace App\Http\Requests;

use App\Models\ProductBrand;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BrandBulkDeleteRequest extends FormRequest
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
                    $brandIds = is_array($val) ? $val : [$val];

                    $query = ProductBrand::whereIn('id', $brandIds)
                        ->where(function ($query) {
                            $query->whereDoesntHave('products');
                        })->count();

                    if($query != count($brandIds))
                    {
                        $fail(__('One or more selected brands can not be deleted.'));
                    }
                }
            ]
        ];
    }


    public function messages()
    {
        return [
            'ids.array'    => __('Please select at least one brand.'),
            'ids.required' => __('Please select at least one brand.'),
        ];
    }



    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }
}
