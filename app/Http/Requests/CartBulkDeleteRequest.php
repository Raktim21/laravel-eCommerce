<?php

namespace App\Http\Requests;

use App\Models\CustomerCart;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class CartBulkDeleteRequest extends FormRequest
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
            'user_unique_id'    => auth()->guard('user-api')->check() ? 'required|exists:customer_carts,guest_session_id' :
                                    'sometimes|exists:customer_carts,guest_session_id',
            'ids'               => ['required','array',
                                    function($attr, $val, $fail) {
                                        foreach ($val as $cart)
                                        {
                                            $isValid = CustomerCart::where('id',$cart)
                                                ->when(auth()->guard('user-api')->check(), function ($query) {
                                                    $query->where('user_id',auth()->guard('user-api')->user()->id);
                                                })
                                                ->when(!auth()->guard('user-api')->check(), function ($query) {
                                                    $query->where('guest_session_id', $this->input('user_unique_id'));
                                                })->exists();

                                            if(!$isValid)
                                            {
                                                $fail(__('You are not authorized to delete the selected carts.'));
                                            }
                                        }
                                    }
                                   ],
           ];
    }



    public function messages()
    {
        return [
            'user_unique_id.required' => __('Please provide the user unique id.'),
            'ids.required'            => __('Please select atleast one cart item.'),
            'ids.array'               => __('Please select atleast one cart item.'),

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
