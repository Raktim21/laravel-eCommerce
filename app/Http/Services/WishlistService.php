<?php

namespace App\Http\Services;

use App\Models\CustomerCart;
use App\Models\Wishlist;
use App\Models\WishListItem;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WishlistService
{
    protected $wish;

    public function __construct(Wishlist $wish)
    {
        $this->wish = $wish;
    }

    public function storeWish(Request $request): bool
    {
        DB::beginTransaction();

        try {
            $new_wish = null;
            if ($request->title) {
                $new_wish = $this->wish->clone()->create([
                    'user_id' => auth()->guard('user-api')->user()->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'secret_key' => uniqid('WISH-', true),
                ]);
            }

            if($new_wish && $new_wish->id)
            {
                WishListItem::firstOrCreate([
                    'wishlist_id' => $new_wish->id,
                    'product_combination_id' => $request->product_combination_id,
                ]);
            } else {
                foreach($request->wishlist as $wish)
                {
                    WishListItem::firstOrCreate([
                        'wishlist_id' => $wish,
                        'product_combination_id' => $request->product_combination_id,
                    ],[
                        'wishlist_id' => $wish,
                        'product_combination_id' => $request->product_combination_id,
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollback();
            return false;
        }
    }

    public function getAuthWish()
    {
        return $this->wish->clone()->with(['items' => function($q1) {
            $q1->with(['productCombination' => function($q) {
                $q->with(['product' => function($q1) {
                    return $q1->select('id','name','thumbnail_image');
                }])->with('attributeValues.attribute');
            }]);
        }])->where('user_id',auth()->user()->id)->latest()->get();
    }

    public function deleteWish($id): bool
    {
        $wish = $this->wish->clone()->findOrFail($id);

        if($wish->user_id == auth()->guard('user-api')->user()->id)
        {
            $wish->delete();
            return true;
        } else {
            return false;
        }
    }

    public function multipleDelete(Request $request): void
    {
        $this->wish->clone()->whereIn('id',$request->ids)->delete();
    }

    public function convertToCart($id): int
    {
        DB::beginTransaction();

        try {
            $wish = $this->wish->clone()->findOrFail($id);

            if($wish->user_id != auth()->guard('user-api')->user()->id) {
                return 0;
            }

            foreach ($wish->items as $item) {

                $cart = CustomerCart::where('user_id', auth()->guard('user-api')->user()->id)
                    ->where('product_combination_id', $item->product_combination_id)->first();

                if($cart) {
                    $cart->product_quantity += 1;
                    $cart->save();
                } else {
                    CustomerCart::create([
                        'user_id'                 => auth()->guard('user-api')->user()->id,
                        'product_combination_id'  => $item->product_combination_id,
                        'product_quantity'        => 1,
                    ]);
                }
            }

            $wish->delete();

            DB::commit();
            return 1;
        } catch(QueryException $ex)
        {
            DB::rollback();
            return 0;
        }
    }

    public function deleteWishItem($id): bool
    {
        $wish_item = WishListItem::findOrFail($id);

        if($wish_item->wish->user_id != auth()->guard('user-api')->user()->id)
        {
            return false;
        } else {
            $wish_item->delete();
            return true;
        }
    }

}
