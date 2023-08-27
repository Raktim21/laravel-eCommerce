<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryStoreRequest extends FormRequest
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
            'name'  => 'required|max:50|unique:product_categories,name',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048'
        ];
    }


    public function messages()
    {
        return [
            'name.required'  => __('Category name is required'),
            'name.unique'    => __('Category name must be unique'),
            'image.required' => __('Please provide a category image'),
            'image.mimes'    => __('Invalid category image.'),
            'image.max'      => __('Category image must be less than 2MB.'),
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
