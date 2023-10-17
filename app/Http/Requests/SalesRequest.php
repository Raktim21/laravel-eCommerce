<?php

namespace App\Http\Requests;

use App\Http\Services\AssetService;
use App\Models\Inventory;
use App\Models\OrderPickupAddress;
use App\Models\UserAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SalesRequest extends FormRequest
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
            'user_id'                               => 'required|exists:users,id',
            'promo_discount'                        => 'nullable|numeric|lte:100',
            'product_combinations.*.quantity'       => 'required|integer|min:1',
            'product_combinations.*.combination_id' => 'required|exists:product_combinations,id|distinct',
            'product_combinations'                  => ['required','array',
                                                        function($attr, $val, $fail) {
                                                            foreach ($val as $item) {
                                                                $inventory = Inventory::where('product_combination_id', $item['combination_id'])
                                                                    ->where('shop_branch_id', auth()->guard('admin-api')->user()->shop_branch_id)
                                                                    ->where('stock_quantity','>=',$item['quantity'])->first();

                                                                if(is_null($inventory)) {
                                                                    $fail(__('Selected product combination is out of stock.'));
                                                                }
                                                            }
                                                        }],
            'delivery_method_id'                    => ['required','in:1,2',
                                                        function($attr,$val,$fail) {
                                                            if($val == 1)
                                                            {
                                                                if (!$this->input('delivery_address_id')) {
                                                                    $fail('The delivery address is required.');
                                                                }
                                                                else {
                                                                    $active_system = (new AssetService())->activeDeliverySystem();

                                                                    $pickup = OrderPickupAddress::where('shop_branch_id', auth()->guard('admin-api')->user()->shop_branch_id)->first();

                                                                    if(!$pickup)
                                                                    {
                                                                        $fail('No pickup address found for your branch.');
                                                                    }

                                                                    if ($active_system == 2)
                                                                    {
                                                                        if ($pickup && !$pickup->hub_id)
                                                                        {
                                                                            $fail('No hub has been configured for eCourier service.');
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }],
            'delivery_address_id'                   => ['sometimes',
                                                        function($attr,$val,$fail) {
                                                            $valid = UserAddress::where('id', $val)->where('user_id', $this->input('user_id'))
                                                                ->where('is_active', 1)
                                                                ->first();

                                                            if(!$valid) {
                                                                $fail('The shipping address is not valid.');
                                                            }
                                                        }],
            'merchant_remarks'                      => 'nullable|string|max:500'
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
