<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InventoryTransferRequest extends FormRequest
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
            'to_branch_id'      => ['required','exists:shop_branches,id',
                function($attr,$val,$fail) {
                    if(!auth()->guard('admin-api')->user()->hasRole('Super Admin') &&
                        auth()->guard('admin-api')->user()->shop_branch_id == $val)
                    {
                        $fail('You can not transfer products to the same branch where they belong.');
                    }
                }],
            'items.*.id'        => 'required|exists:inventories,id|distinct',
            'items.*.quantity'  => 'required|integer|min:1',
            'items'             => ['required','array',
                                    function($attr, $val, $fail) {
                                        foreach ($val as $key => $item) {
                                            $inventory = Inventory::find($item['id']);

                                            if($key == 0 && !is_null($inventory)) {
                                                $from_branch = $inventory->shop_branch_id;
                                            }

                                            if(is_null($inventory) ||
                                                ($inventory->shop_branch_id != auth()->guard('admin-api')->user()->shop_branch_id &&
                                                !auth()->guard('admin-api')->user()->hasRole('Super Admin'))) {
                                                    $fail('Some of the selected inventories are invalid.');
                                            }
                                            else if($inventory->stock_quantity < $item['quantity']) {
                                                $fail('Some of the selected inventories have insufficient quantity.');
                                            }
                                            else if($inventory->shop_branch_id == $this->input('to_branch_id')) {
                                                $fail('You can not transfer products to the same branch where they belong.');
                                            }
                                            else if($key != 0 && $from_branch != $inventory->shop_branch_id) {
                                                $fail('Selected products must remain in the same branch.');
                                            }
                                        }
                                    }],
        ];
    }

    public function messages()
    {
        return [
            'to_branch_id.required'     => 'Please select a branch to transfer the stock.',
            'items.*.quantity.min'      => 'Selected stock quantity must be at least 1.'
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
