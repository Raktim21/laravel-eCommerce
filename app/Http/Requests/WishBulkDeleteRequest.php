<?php

namespace App\Http\Requests;

use App\Models\Wishlist;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WishBulkDeleteRequest extends FormRequest
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
            'ids'       => ['required','array',
                            function($attr,$val,$fail) {
                                $list = Wishlist::whereIn('id',$val)
                                    ->where('user_id',auth()->user()->id)
                                    ->where('is_ordered',0)->get();

                                if(count($list) !== count($val))
                                {
                                    $fail('Some of the selected products can not be deleted from wishlist.');
                                }
                            }],
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
