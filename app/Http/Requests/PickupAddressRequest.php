<?php

namespace App\Http\Requests;

use App\Models\Union;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Districts;
use App\Models\Division;
use App\Models\Upazila;
use Illuminate\Support\Facades\Log;

class PickupAddressRequest extends FormRequest
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
            'name'            => 'required|string|max:200',
            'phone'           => [
                                    'required',
                                    'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                                    'string',
                                ],
            'email'           => 'required|email|max:100',
            'upazila_id'      => 'required|numeric|exists:location_upazilas,id',
            'union_id'        => ['required',
                                    function($attr, $val, $fail) {
                                        $union = Union::where('upazila_id', $this->input('upazila_id'))->first();

                                        if(is_null($union)) {
                                            $fail('Selected union is invalid.');
                                        }
                                    }],
            'address'         => 'required|string|max:500',
            'postal_code'     => 'required',
        ];

    }


    public function messages()
    {
        return [
            'name.required'            => __('Please provide a name'),
            'name.string'              => __('Provided name is invalid'),
            'name.max'                 => __('Your provided name is invalid'),
            'phone.required'           => __('Please provide a phone number'),
            'phone.max'                => __('Your provided phone number is invalid'),
            'phone.min'                => __('Your provided phone number is invalid'),
            'phone.regex'              => __('Your provided phone number is invalid'),
            'email.required'           => __('Please provide an email'),
            'email.email'              => __('Provided email is invalid'),
            'email.max'                => __('Your provided email is invalid'),
            'address.required'         => __('Address is required'),
            'address.string'           => __('Provided address is invalid'),
            'address.max'              => __('Address can not have more than 255 characters'),
            'postal_code.required'     => __('Postal code is required'),

        ];
    }



    protected function passedValidation()
    {
        $this->replace([
            'name'            => $this->name,
            'phone'           => $this->phone,
            'email'           => $this->email,
            'upazila_id'      => $this->upazila_id,
            'union_id'        => $this->union_id,
            'address'         => $this->address,
            'postal_code'     => $this->postal_code,
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }
}
