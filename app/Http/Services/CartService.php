<?php

namespace App\Http\Services;

use App\Models\CustomerCart;
use App\Models\Inventory;
use App\Models\OrderAdditionalCharge;
use App\Models\Wishlist;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartService
{

    protected $cart;

    public function __construct(CustomerCart $cart)
    {
        $this->cart = $cart;
    }

    public function getCart()
    {
        if (!auth()->guard('user-api')->check() && !request()->cookie('customer_unique_token')){
            return [];
        }

        return $this->cart->clone()
            ->when(!auth()->guard('user-api')->check(), function($q) {
                return $q->where('guest_session_id', request()->cookie('customer_unique_token'));
            })
            ->when(auth()->guard('user-api')->check(), function ($q) {
                return $q->where('user_id', auth()->guard('user-api')->user()->id);
            })->with(['productCombination' => function($q) {
                $q->with('attributeValues.attribute')
                  ->with(['product' => function($q) {
                        $q->select('id','name','slug','uuid','thumbnail_image');
                }]);
            }])
            ->get();
    }

    public function storeCart(Request $request)
    {
        DB::beginTransaction();

        try{
            if(auth()->guard('user-api')->check())
            {
                $exist_product = $this->cart->clone()->where('user_id', auth()->guard('user-api')->user()->id)
                    ->where('product_combination_id',$request->product_combination_id)->first();
            } else {
                $exist_product = request()->cookie('customer_unique_token') ? $this->cart->clone()
                    ->where('guest_session_id', request()->cookie('customer_unique_token'))
                    ->where('product_combination_id', $request->product_combination_id)->first() : null;
            }

            $this->cartAddUpdate($request, $exist_product);

            DB::commit();
            return $this->cart->guest_session_id;
        }
        catch(QueryException $e)
        {
            DB::rollback();
            return 'error';
        }
    }

    public function updateCart(Request $request, $id): bool
    {
        $user_cart = $this->cart->clone()
            ->when(auth()->guard('user-api')->check(), function ($q) {
                return $q->where('user_id', auth()->user()->id);
            })
            ->when(!auth()->guard('user-api')->check(), function ($q) use($request) {
                return $q->where('guest_session_id', request()->cookie('customer_unique_token'));
            })->where('id', $id)->first();

        if ($user_cart) {
            $user_cart->product_quantity = $request->quantity;
            $user_cart->save();
            return true;

        }else {
            return false;
        }
    }

    public function deleteCart($id): bool
    {
        if (auth()->guard('user-api')->check()) {
            $cart = $this->cart->clone()->where('user_id', auth()->guard('user-api')->user()->id)->where('id', $id)->first();
        }else {
            $cart = $this->cart->clone()->where('guest_session_id', request()->cookie('customer_unique_token'))->where('id', $id)->first();
        }

        if ($cart) {
            $cart->delete();
            return true;
        }else {
            return false;
        }
    }

    public function multipleDeletes(Request $request)
    {
        $this->cart->clone()->whereIn('id', $request->ids)->delete();
    }

    private function cartAddUpdate($request, $exist_product = null): void
    {
        if ($exist_product)
        {
            $exist_product->product_quantity += $request->quantity;
            $exist_product->save();
        }
        else
        {
            $this->cart->product_combination_id  = $request->product_combination_id;
            $this->cart->product_quantity        = $request->quantity;

            if (auth()->guard('user-api')->check()) {
                $this->cart->user_id             = auth()->guard('user-api')->user()->id;
            } else {
                $this->cart->guest_session_id    = request()->cookie('customer_unique_token') ?? uniqid('GUEST-');
            }

            $this->cart->save();
        }
    }

    public function convertToAuthCart(Request $request): void
    {
        if($request->status == 1){
            $data = $this->cart->clone()->where('guest_session_id', request()->cookie('customer_unique_token'))->get();

            foreach ($data as $item) {
                $cart = $this->cart->clone()->where('user_id', auth()->guard('user-api')->user()->id)
                    ->where('product_combination_id', $item->product_combination_id)->first();

                if ($cart) {
                    $cart->product_quantity += $item->product_quantity;
                    $cart->save();
                    $data->delete();
                } else {
                    $item->user_id = auth()->guard('user-api')->user()->id;
                    $item->guest_session_id = null;
                    $item->save();
                }
            }
        } else {
            $this->cart->clone()->where('guest_session_id', request()->cookie('customer_unique_token'))->delete();
        }
    }



    public function addCartFromWishlist(Request $request) {

        $wishlist = Wishlist::where('secret_key', $request->wishlist_secret_key)->first();

        if(is_null($wishlist) || count($wishlist->items) == 0)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Wishlist Not Found']
            ], 404);
        }

        $total_item = count($wishlist->items);
        $added_item = 0;

        DB::beginTransaction();

        try {
            if (auth()->guard('user-api')->check()) {
                foreach ($wishlist->items as $item) {

                    $is_valid = Inventory::where('product_combination_id', $item->product_combination_id)->where('stock_quantity', '>', 0)->exists();

                    if ($is_valid) {
                        $added_item += 1;
                        $exist_product = $this->cart->clone()->where('user_id', auth()->guard('user-api')->user()->id)->where('product_combination_id', $item->product_combination_id)->first();

                        if ($exist_product) {
                            $exist_product->product_quantity += 1;
                            $exist_product->save();
                        } else {
                            $this->cart->product_combination_id = $item->product_combination_id;
                            $this->cart->product_quantity = 1;
                            $this->cart->user_id = auth()->guard('user-api')->user()->id;
                            $this->cart->save();
                        }
                    }
                }
                DB::commit();

                if($added_item == 0)
                {
                    return response()->json([
                        'status' => false,
                        'errors' => ['The items are currently not available.']
                    ], 422);
                } else {
                    $s = $total_item . $total_item == 1 ? ' item has' : ' items have';
                    return response()->json([
                        'status' => true,
                        'message'=> $total_item==$added_item ? 'All items have been added to cart.' : $added_item . ' out of ' . $s . ' been added to cart.'
                    ]);
                }
            } else {
                foreach ($wishlist->items as $item) {

                    $is_valid = Inventory::where('product_combination_id', $item->product_combination_id)->where('stock_quantity', '>', 0)->exists();

                    if ($is_valid) {
                        $added_item += 1;
                        $exist_product = !is_null(request()->cookie('customer_unique_token')) ?
                            $this->cart->clone()->where('guest_session_id', request()->cookie('customer_unique_token'))
                                ->where('product_combination_id', $item->product_combination_id)->first() : null;

                        if ($exist_product) {
                            $exist_product->product_quantity += 1;
                            $exist_product->save();
                        } else {
                            $this->cart->product_combination_id = $item->product_combination_id;
                            $this->cart->product_quantity = 1;
                            $this->cart->guest_session_id = request()->cookie('customer_unique_token') ?? uniqid('GUEST-');
                            $this->cart->save();
                        }
                    }
                }
                DB::commit();
                if($added_item == 0)
                {
                    return response()->json([
                        'status' => false,
                        'errors' => ['The items are currently not available.']
                    ], 422);
                }
                else if(is_null(request()->cookie('customer_unique_token')))
                {
                    $s = $total_item == 1 ? ' item has' : ' items have';
                    return response()->json([
                        'status'  => true,
                        'message' => $total_item==$added_item ? 'All items have been added to cart.' : $added_item . ' out of ' . $total_item . $s . ' been added to cart.'
                    ])->cookie('customer_unique_token', $this->cart->guest_session_id, 43200, null, null, false, false);
                } else {
                    $s = $total_item == 1 ? ' item has' : ' items have';

                    return response()->json([
                        'status' => true,
                        'message'=> $total_item==$added_item ? 'All items have been added to cart.' : $added_item . ' out of ' . $total_item . $s . ' been added to cart.'
                    ]);
                }
            }
        } catch(QueryException $ex)
        {
            DB::rollback();

            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.']
            ], 500);
        }
    }

    public function getAdditionalCharge()
    {
        return OrderAdditionalCharge::where('status', 1)->get();
    }
}
