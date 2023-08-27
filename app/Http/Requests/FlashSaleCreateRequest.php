<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FlashSaleCreateRequest extends FormRequest
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
            'title'              => 'required|string|max:150',
            'start_date'         => 'required|date|date_format:Y-m-d H:i:s|before:end_date|after_or_equal:'.date('Y-m-d H:i:s'),
            'end_date'           => 'required|date|date_format:Y-m-d H:i:s|after:start_date|after_or_equal:'.date('Y-m-d H:i:s'),
            'short_description'  => 'nullable|string|max:400',
            'image'              => 'sometimes|nullable|image|mime:jpeg,png,jpg,gif,svg|max:1028',
            'discount'           => 'required|nullable|numeric|max:100|min:0',
            'products'           => 'required|array|min:1',
            'products.*'         => 'required|exists:products,id',
            'status'             => 'sometimes|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'title.required'              => 'Please provide a title for this flash sale section',
            'title.string'                => 'Title must be a string',
            'title.max'                   => 'Title must be less than 150 characters',
            'start_date.required'         => 'Please provide a start date for this flash sale section',
            'start_date.date'             => 'Start date must be a valid date',
            'end_date.required'           => 'Please provide an end date for this flash sale section',
            'end_date.date'               => 'End date must be a valid date',
            'short_description.string'    => 'Description must be a string',
            'short_description.max'       => 'Description must be less than 400 characters',
            'image.image'                 => 'Unsupportable image',
            'image.mimes'                 => 'Unsupportable image',
            'discount.integer'            => 'Please provide a valid discount',
            'discount.max'                => 'Discount must be less than 100',
            'discount.min'                => 'Discount must be greater than 0',
            'products.required'           => 'Please provide a product for this flash sale section',
            'products.array'              => 'Product must be an array',
            'products.*.required'         => 'Please provide a product for this flash sale section',
            'products.*.integer'          => 'Product must be an integer',
            'products.*.exists'           => 'Product does not exist',
            'status.in'                   => 'Status must be a boolean',
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
