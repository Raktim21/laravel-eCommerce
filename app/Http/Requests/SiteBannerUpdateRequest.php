<?php

namespace App\Http\Requests;

use App\Models\GalleryHasImage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SiteBannerUpdateRequest extends FormRequest
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
        $gallery = new GalleryHasImage();

        return [
            'flash_sale_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'new_arrival_image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount_product_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'popular_product_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'newsletter_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'featured_banner_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'all_product_side_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'featured_product_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            'flash_sale_image_id'      => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
            'new_arrival_image_id'      => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
            'discount_product_image_id' => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
            'popular_product_image_id'  => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
            'newsletter_image_id'       => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
            'featured_banner_image_id'  => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
            'all_product_side_image_id'  => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
            'featured_product_image_id'  => ['sometimes','integer',
                                            function($attr, $val, $fail) use ($gallery) {
                                                $img = $gallery->find($val);

                                                if (!$img)
                                                {
                                                    $fail('No image found.');
                                                }

                                                else if ($img->gallery->user_id != auth()->user()->id && $img->is_public == 0) {
                                                    $fail('You cannot select an image from private folder.');
                                                }
                                            }],
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
