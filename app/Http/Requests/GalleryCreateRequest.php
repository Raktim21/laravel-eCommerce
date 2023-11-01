<?php

namespace App\Http\Requests;

use App\Models\Gallery;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GalleryCreateRequest extends FormRequest
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
            'is_public' => 'required|in:0,1',
            'name'      => ['required','string','max:100',
                            function($attr, $val, $fail) {
                                $gallery = Gallery::where('name', $val)
                                    ->where('user_id', auth()->user()->id)
                                    ->first();

                                if ($gallery && $gallery->id != $this->route('id'))
                                {
                                    $fail('You already have a gallery with same name.');
                                }
                            }],
            'images'    => 'sometimes|array|min:1',
            'images.*'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
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
