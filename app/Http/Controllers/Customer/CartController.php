<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartCreateRequest;
use App\Http\Requests\CartBulkDeleteRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Http\Services\AssetService;
use App\Http\Services\CartService;
use App\Http\Services\OrderDeliverySystemService;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    protected $service;

    public function __construct(CartService $service)
    {
        $this->service = $service;
    }


    public function cartList()
    {
        $data = $this->service->getCart();

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], count($data) == 0 ? 204 : 200);
    }



    public function cartStore(CartCreateRequest $request)
    {
        $response = $this->service->storeCart($request);

        if($response != 'error')
        {
            if(request()->cookie('customer_unique_token'))
            {
                return response()->json([
                    'status' => true,
                ], 201);
            }
            return response()->json([
                'status'         => true,
            ], 201)->cookie('customer_unique_token', $response, 43200, null, null, false, false);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.'],
            ], 500);
        }
    }

    public function addUserCart(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'status'        => 'required|in:0,1'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status'    => false,
                'errors'    => $validate->errors()->all(),
            ], 422);
        }

        $this->service->convertToAuthCart($request);

        return response()->json([
            'status' => true,
        ])->cookie('customer_unique_token', null, 43200, null, null, true, true)
            ->header('Access-Control-Allow-Headers', '*, Authorization, X-Authorization');
    }



    public function cartUpdate(CartUpdateRequest $request,$id)
    {
        if($this->service->updateCart($request, $id))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['Cart is invalid.'],
            ],401);
        }
    }


    public function cartDelete($id)
    {
        if(!auth()->guard('user-api')->check() && !request()->cookie('customer_unique_token'))
        {
            return response()->json([
                'status' => false,
                'errors' => ['Invalid cart.'],
            ], 400);
        }
        if($this->service->deleteCart($id))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        else {
            return response()->json([
                'status' => false,
                'errors' => ['You can not delete this cart.'],
            ], 403);
        }
    }

    public function bulkDelete(CartBulkDeleteRequest $request)
    {
        $this->service->multipleDeletes($request);

        return response()->json([
            'status' => true,
        ]);
    }


    public function deliveryCharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => ['required',
                            function ($attr, $val, $fail) {
                                $valid = UserAddress::where('user_id', auth()->user()->id)
                                    ->where('id', $val)->first();

                                if(!$valid)
                                {
                                    $fail('Invalid address.');
                                }
                            }],
            'total_price'=> 'required|numeric'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $data = (new OrderDeliverySystemService())
            ->getDeliveryCharge((new AssetService())->activeDeliverySystem(), $request->address_id, $request->total_price);

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    public function getCharge()
    {
        $charge = $this->service->getAdditionalCharge();

        return response()->json([
            'status'        => true,
            'data'          => $charge
        ], count($charge)==0 ? 204 : 200);
    }


    public function addCartFromWishlist(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'wishlist_secret_key' => 'required|exists:wishlists,secret_key',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all(),
            ], 422);
        }

        return $this->service->addCartFromWishlist($request);
    }

}
