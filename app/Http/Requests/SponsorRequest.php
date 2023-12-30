<?php

namespace App\Http\Requests;

use App\Models\GalleryHasImage;
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
        return [
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
            'image_id' => ['sometimes','integer',
                            function($attr, $val, $fail) {
                                $img = GalleryHasImage::find($val);

                                if (!$img)
                                {
                                    $fail('No image found.');
                                }

                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                    $fail('You cannot select an image from private folder.');
                                }
                            }]
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
