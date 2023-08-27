<?php

namespace App\Http\Requests;

use App\Models\CustomerCart;
use App\Models\ProductHasPromo;
use App\Models\PromoCode;
use App\Models\PromoProduct;
use App\Models\PromoUser;
use App\Models\UserAddress;
use App\Models\UserPromo;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class UserOrderRequest extends FormRequest
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
            'payment_method_id'   =>  'required|exists:order_payment_methods,id',
            'promo_code_id'       =>    ['sometimes','exists:promo_codes,id',
                                        function($attr,$val,$fail) {
                                            $promo = PromoCode::find($val);

                                            if($promo->is_active == 0 || !Carbon::parse($promo->start_date)->lessThanOrEqualTo(Carbon::today()) ||
                                                (!is_null($promo->end_date) && !Carbon::parse($promo->end_date)->greaterThanOrEqualTo(Carbon::today()))) {
                                                $fail('The selected promo code is invalid.');
                                            }
                                            if($promo->is_global_product == 0) {
                                                $products = PromoProduct::where('promo_id',$promo->id)->select('product_id')->get();
                                                $cart_products = DB::table('customer_carts')
                                                    ->leftJoin('product_combinations','customer_carts.product_combination_id','=','product_combinations.id')
                                                    ->where('user_id',auth()->user()->id)->select('product_combinations.product_id as product_id')->get();

                                                $matches = collect($products)->pluck('product_id')->intersect(collect($cart_products)->pluck('product_id'));

                                                if ($matches->isEmpty())
                                                {
                                                    $fail('The selected promo code is not applicable.');
                                                }
                                            }
                                            if($promo->max_num_users!=0) {
                                                if(CustomerCart::where('user_id', auth()->user()->id)->count() !=
                                                    CustomerCart::where('user_id', auth()->user()->id)->where('product_quantity', 1)->count()) {
                                                    $fail('Please order only one item of each cart product.');
                                                }
                                                if(PromoUser::where('promo_id', $val)->count() == $promo->max_num_users) {
                                                    $fail('Selected promo code has exceeded maximum number of users.');
                                                }
                                            }
                                            $valid_user = PromoUser::where('user_id', auth()->user()->id)->where('promo_id', $promo->id)->first();

                                            if($promo->is_global_user == 0) {

                                                if(is_null($valid_user)) {
                                                    $fail('The selected promo code is not applicable.');
                                                }
                                            }
                                            if($promo->max_usage!=0 && $promo->max_usage <= $valid_user->usage_number)  {
                                                $fail('The selected promo code is not applicable.');
                                            }
                                        }],
            'delivery_address_id' =>  ['required',
                                        function($attr, $val, $fail) {
                                            $address = UserAddress::where('user_id', auth()->user()->id)
                                                ->where('id', $val)->where('is_active',1)->first();

                                            if(is_null($address)) {
                                                $fail('The selected address is invalid.');
                                            }
                                        }],
            'delivery_remarks'      =>  'string',
        ];
    }


    public function messages()
    {
        return [
            'payment_method_id.required'   => 'Please select a payment method',
            'delivery_method_id.required'  => 'Please select a shipping method',
            'delivery_address_id.required' => 'Please select a shipping address',
            'delivery_remarks.string'        => 'Please enter a valid delivery notes',
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
