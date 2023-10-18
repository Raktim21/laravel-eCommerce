<?php

namespace App\Http\Requests;

use App\Http\Services\AssetService;
use App\Models\OrderPickupAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class AdminOrderStatusChangeRequest extends FormRequest
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
            'status'            => 'required|exists:order_statuses,id',
            'shop_branch_id'    => ['required','exists:shop_branches,id',
                                    function($attr, $val, $fail) {
                                        if ($this->input('status') == 2)
                                        {
                                            $active_system = (new AssetService())->activeDeliverySystem();

                                            $pickup = OrderPickupAddress::where('shop_branch_id', $val)->first();

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
                                    }],
            'reason'            => 'required_if:status,3|in:DELIVERY_ETA_TOO_LONG,MISTAKE_ERROR,REASON_UNKNOWN'
        ];
    }


    public function messages()
    {
        return [
            'status.required' => __('Please provide the order status'),
            'status.exists'   => __('Order status is invalid'),
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

