<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StaticPageUpdateRequest extends FormRequest
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
            'name'         => 'required|string|max:50|unique:static_pages,name,'.$this->id,
            'containt'     => 'required|string',
            'is_active'    => 'required|integer|in:1,0',
            'is_on_header' => 'required|integer|in:1,0',
            'is_on_footer' => 'required|integer|in:1,0',
        ];
    }


    public function messages()
    {
        return [
            'name.required'         => __('The name field is required.'),
            'name.string'           => __('Unsupportable name.'),
            'name.max'              => __('The name field must be less than or equal to 50 characters.'),
            'name.unique'           => __('The name field must be unique.'),
            'containt.required'     => __('The containt field is required.'),
            'is_active.required'    => __('The is_active field is required.'),
            'is_active.in'          => __('The is_active field must be 1 or 0.'),
            'is_on_header.required' => __('Please select do you want to show on header or not.'),
            'is_on_header.in'       => __('Please select do you want to show on header or not.'),
            'is_on_footer.required' => __('Please select do you want to show on footer or not.'),
            'is_on_footer.in'       => __('Please select do you want to show on footer or not.'),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors()->all()
        ], 422));
    }
}
