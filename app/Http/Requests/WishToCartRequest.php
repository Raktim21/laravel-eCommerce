<?php

namespace App\Http\Requests;

use App\Models\AttributeVariant;
use App\Models\Inventory;
use App\Models\ProductCombination;
use App\Models\Wishlist;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WishToCartRequest extends FormRequest
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
            'ids' => ['required','array'],
            'ids.*' => ['required',
                function($attr,$val,$fail) {
                    $wish = Wishlist::find($val);

                    if(is_null($wish))
                    {
                        $fail('The selected wishlist is invalid.');
                    }
                    else {
                        if($wish->user_id != auth()->user()->id)
                        {
                            $fail('You are not authorized to add the wishlist to cart.');
                        }
                        else
                        {
                            $stock = Inventory::where('product_combination_id', $wish->product_combination_id)
                                ->where('quantity','>=',1)->first();

                            if(is_null($stock)) {
                                $fail($wish->productCombination->product->name . ' is out of stock.');
                            }
                        }
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
