<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use App\Models\ProductCombination;
use App\Models\PromoCode;
use App\Models\PromoProduct;
use App\Models\PromoUser;
use App\Models\Union;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;

class MessengerOrderRequest extends FormRequest
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
            'delivery_method_id'  =>  'required|exists:order_delivery_methods,id',
            'user_sex_id'         =>  'required|exists:user_sexes,id',
            'email'               =>  'required|email',
            'name'                =>  'required|string|max:100',
            'messenger_psid'      =>   'required|string|max:50',
            'password'            =>  ['required','string',
                                        function($attr, $val, $fail) {
                                            $user = User::where('username', $this->input('email'))->withTrashed()->first();
                                            if(!is_null($user) && !Hash::check($val, $user->password)) {
                                                $fail('The password field does not match.');
                                            }

                                            if ($user && !is_null($user->shop_branch_id))
                                            {
                                                $fail('Invalid user.');
                                            }
                                        }],
            'order_items.*.product_attribute_combination_id'    => ['required',
                                                                    function($attr,$val,$fail) {
                                                                        $combo = ProductCombination::find($val);

                                                                        if(is_null($combo) || $combo->is_active == 0) {
                                                                            $fail('Some of the selected product combination is currently unavailable.');
                                                                        }
                                                                    }],
            'order_items.*.product_quantity'    => 'required|integer|min:1',
            'order_items'         => ['required','array',
                function($attr, $val, $fail) {
                    $weight = 0;
                    foreach ($val as $item) {
                        $inventory = Inventory::where('product_combination_id', $item['product_attribute_combination_id'])
                            ->where('stock_quantity','>=',$item['product_quantity'])->first();

                        $combo = ProductCombination::find($item['product_attribute_combination_id']);

                        if(is_null($inventory)) {
                            $fail('Some of the selected product combination is out of stock.');
                        }

                        if(is_null($combo))
                        {
                            $fail('Invalid product combination.');
                        } else {
                            $weight += $combo->weight * $item['product_quantity'];
                        }
                    }

                    if($weight > 5)
                    {
                        $fail('You can not place an order that weighs over 5 KG.');
                    }
                }],
            'promo_code'          =>  ['sometimes',
                                        function($attr, $val, $fail) {
                                            $code = PromoCode::where('code', $val)->first();
                                            $user = User::where('username', $this->input('email'))->first();

                                            if(is_null($code) || ($code->is_global_user == 0 && is_null($user))) {
                                                $fail('Selected promo code is invalid.');
                                            }
                                            if(!is_null($code) && ($code->is_active == 0 || !Carbon::parse($code->start_date)->lessThanOrEqualTo(Carbon::today()) ||
                                                (!is_null($code->end_date) && !Carbon::parse($code->end_date)->greaterThanOrEqualTo(Carbon::today())))) {
                                                $fail('Selected promo code is invalid.');
                                            }
                                            if (!is_null($code) && $code->max_num_users != 0 && !is_null($user)) {
                                                if(PromoUser::where('promo_id', $code->id)->count() == $code->max_num_users) {
                                                    $fail('Selected promo code has exceeded maximum number of users.');
                                                }
                                            }
                                            if(!is_null($code) && !is_null($user)) {
                                                $valid_user = PromoUser::where('user_id', $user->id)->where('promo_id', $code->id)->first();

                                                if($code->is_global_user == 0 && is_null($valid_user)) {
                                                    $fail('Selected promo code is not applicable.');
                                                }

                                                else if($code->max_usage!=0 && $code->max_usage == $valid_user->usage_number)  {
                                                    $fail('Selected promo code is not applicable.');
                                                }
                                            }
                                            if(!is_null($code) && $code->is_global_product == 0) {
                                                $products = PromoProduct::where('promo_id',$code->id)->select('product_id')->get();
                                                $cart_products = $this->input('order_items');

                                                $matches = collect($products)->pluck('product_id')->intersect(collect($cart_products)->pluck('product_id'));

                                                if ($matches->isEmpty())
                                                {
                                                    $fail('The selected promo code is not applicable.');
                                                }
                                            }
                                        }],
            'upazila_id'          =>   'required|exists:location_upazilas,id',
            'union_id'            =>  ['sometimes',
                                        function($attr, $val, $fail) {
                                            $union = Union::where('upazila_id', $this->input('upazila_id'))
                                            ->where('id', $val)->first();

                                            if(is_null($union)) {
                                                $fail('Selected union is invalid.');
                                            }
                                        }],
            'postal_code'         =>  'required|string|max:40',
            'area'                =>  'required|string|max:35',
            'address'             =>  'nullable|string',
            'lat'                 =>  'required|max:20',
            'lng'                 =>  'required|max:20',
            'delivery_remarks'    =>  'nullable|string',
            'phone_no'            =>  ['required','string','regex:/^(?:\+88|88)?(01[3-9]\d{8})$/'],
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
