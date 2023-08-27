<?php

namespace App\Http\Requests;

use App\Models\Sponsor;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SponsorRequest extends FormRequest
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
        $rules = [
            'name'  => ['required','string','max:100',
                        function($attr, $val, $fail) {
                            $sponsor = Sponsor::where('name', $val)->first();

                            if($sponsor && ($sponsor->id != $this->route('id')))
                            {
                                $fail('Selected sponsor already exists.');
                            }
                        }],
            'url'   => 'nullable|url|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        if(!$this->route('id'))
        {
            $rules['image'] = 'required';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }
}
