<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Services\AssetService;
use App\Models\MerchantPaymentInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MerchantPaymentController extends Controller
{
    public function getInfo()
    {
        $system = (new AssetService())->activeDeliverySystem();

        $data = Cache::remember('merchantPaymentInfo', 24*60*60*7, function () use ($system) {
            return MerchantPaymentInfo::where('delivery_system_id', $system)->with('payment_method')->first();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], is_null($data) ? 204: 200);
    }

    public function updateInfo(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'payment_method_id'  => 'required|exists:merchant_payment_methods,id',
            'bank_branch_id'     => 'required_if:payment_method_id,1,2|exists:bank_branches,id',
            'bank_account_holder'=> 'required_if:payment_method_id,1,2|string|max:100',
            'bank_account_no'    => 'required_if:payment_method_id,1,2|string|max:30',
            'bkash_no'           => ['required_if:payment_method_id,3','string',
                                    'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/'],
            'rocket_no'          => ['required_if:payment_method_id,4','string',
                                    'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/']
        ]);

        if ($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        $system_id = (new AssetService())->activeDeliverySystem();

        $info = MerchantPaymentInfo::where('delivery_system_id', $system_id)->first();

        if(!$info)
        {
            $info = new MerchantPaymentInfo();
        }

        $info->delivery_system_id      = $system_id;
        $info->payment_method_id       = $request->payment_method_id;
        $info->bank_branch_id          = ($request->payment_method_id == 1 || $request->payment_method_id == 2) ?
                                            $request->bank_branch_id : null;
        $info->bank_account_holder     = ($request->payment_method_id == 1 || $request->payment_method_id == 2) ?
                                            $request->bank_account_holder : null;
        $info->bank_account_no         = ($request->payment_method_id == 1 || $request->payment_method_id == 2) ?
                                            $request->bank_account_no : null;
        $info->bkash_no                = $request->payment_method_id == 3 ?
                                            $request->bkash_no : null;
        $info->rocket_no               = $request->payment_method_id == 4 ?
                                            $request->rocket_no : null;
        $info->save();

        Cache::delete('merchantPaymentInfo');

        return response()->json(['status' => true], 201);
    }
}
