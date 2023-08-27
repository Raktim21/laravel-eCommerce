<?php

namespace App\Http\Requests;

use App\Models\ProductSubCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubCategoryRequest extends FormRequest
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
            'category_id' => 'required|exists:product_categories,id',
            'name'        => ['required',
                                function($attr, $val, $fail) {
                                    $subCat = ProductSubCategory::where('name', $val)
                                        ->where('category_id',$this->input('category_id'))->first();

                                    if($subCat && ($subCat->id != $this->route('id')))
                                    {
                                        $fail('This sub category already exists.');
                                    }
                                }],
            'image'       => 'sometimes|nullable|image|mimes:jpg,png,jpeg|max:2048'
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
