<?php

namespace App\Http\Requests;

use App\Models\AttributeVariant;
use App\Models\Inventory;
use App\Models\ProductCombination;
use App\Models\Wishlist;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class WishStoreRequest extends FormRequest
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
            'wishlist'                  => 'sometimes|array',
            'wishlist.*'                => ['required',
                                            function($attr, $val, $fail) {
                                                $wish = Wishlist::where('id',$val)
                                                    ->where('user_id',auth()->guard('user-api')->user()->id)
                                                    ->first();

                                                if(is_null($wish))
                                                {
                                                    $fail('Some of the selected wishlists are invalid.');
                                                }
                                            }],
            'product_combination_id'    => ['required',
                                            function($attr, $val, $fail) {
                                                $stock = Inventory::where('product_combination_id', $val)
                                                ->whereNot('stock_quantity',0)->first();

                                                if(!$stock) {
                                                    $fail('Selected product combination is out of stock.');
                                                }
                                            }],
            'title'                     => ['sometimes','string','max:100',
                                            function($attr, $val, $fail) {
                                                $wish = Wishlist::where('title', $val)
                                                    ->where('user_id', auth()->guard('user-api')->user()->id)->first();

                                                if(!is_null($wish)) {
                                                    $fail('The selected title already exists.');
                                                }
                                            }],
            'description'               => 'sometimes|string|max:500',
        ];

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
