<?php

namespace App\Http\Requests;

use App\Models\Districts;
use App\Models\Division;
use App\Models\Union;
use App\Models\Upazila;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserAddressCreateRequest extends FormRequest
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
            'upazila_id'        => 'required|exists:location_upazilas,id',
            'union_id'          => ['nullable','exists:location_unions,id',
                                    function($attr, $val, $fail) {
                                        $union = Union::where('upazila_id',$this->input('upazila_id'))
                                            ->where('id',$val)->first();

                                        if(is_null($union))
                                        {
                                            $fail('The selected union is invalid.');
                                        }
                                    }
                                 ],
            'address'         => 'required|string|max:255',
            'postal_code'     => 'required|string|max:50',
            'phone_no'        => ['required', 'regex:/^(?:\+88|88)?(01[3-9]\d{8})$/'],
            'is_default'      => 'required|in:0,1',
        ];

    }


    public function messages()
    {
        return [
            'upazila_id.required'      => 'Please select a thana.',
            'union_id.required'        => 'Please select a union.',
            'address.required'         => 'Please enter an address.',
            'postal_code.required'     => 'Please enter a postal code.',
            'is_default.required'      => 'Please select a default address.',
            'is_default.boolean'       => 'Please select a default address.',
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
