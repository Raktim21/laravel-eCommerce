<?php

namespace App\Http\Controllers\Customer\Order;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\PromoUser;
use App\Models\PromoCode;
use App\Models\OrderItems;
use App\Models\PromoProduct;
use Illuminate\Http\Request;
use App\Models\CustomerCart;
use App\Models\ProductReview;
use App\Models\ProductCombination;
use App\Models\ProductReviewImage;
use Illuminate\Support\Facades\DB;
use App\Http\Services\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use App\Http\Services\PromoCodeService;
use Illuminate\Database\QueryException;
use App\Http\Requests\UserOrderRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\OrderDeliverySystemService;

class OrderController extends Controller
{
    protected $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function addPromo(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'promo_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        if(CustomerCart::where('user_id', auth()->user()->id)->count() == 0)
        {
            return response()->json([
                'status'    => false,
                'errors'    => ['No product is added to cart.']
            ], 400);
        }

        $promo = PromoCode::where('code', $request->promo_code)->first();

        if(!$promo)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Promo code not found.']
            ], 404);
        }

        if($this->validatePromoCode($promo))
        {
            return response()->json([
                'status'    => true,
                'data'      => array(
                    'promo_id' => $promo->id,
                    'promo_discount' => $this->service->getPromoDiscount($promo))
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'errors'    => ['The selected promo code is invalid.']
            ], 400);
        }
    }


    public function order(UserOrderRequest $request)
    {
        $cart_items = CustomerCart::where('user_id', auth()->guard('user-api')->user()->id)->get();

        if ($cart_items->count() == 0) {
            return response()->json([
                'status' => false,
                'errors' => ['Cart is empty.']
            ], 400);
        }

        $total_weight = 0;

        foreach ($cart_items as $cart_item) {

            $stock = Inventory::where('product_combination_id', $cart_item['product_combination_id'])
                ->where('stock_quantity','>=',$cart_item['product_quantity'])->first();

            if(is_null($stock)) {
                return response()->json([
                    'status' => false,
                    'errors' => ['The selected quantity of ' . $cart_item->productCombination->product->name . ' is not available.']
                ], 422);
            }

            $combo = ProductCombination::find($cart_item['product_combination_id']);

            if(is_null($combo) || $combo->is_active == 0) {
                return response()->json([
                    'status' => false,
                    'errors' => ['Selected combination of ' . $cart_item->productCombination->product->name . ' is currently unavailable.']
                ], 422);
            }

            $total_weight += $cart_item->product_quantity * $cart_item->productCombination->weight;
        }

        if ($total_weight > 1) {
            return response()->json([
                'status' => false,
                'errors' => ['You cannot place order that weighs over 1KG.'],
            ],422);
        }
        if($this->service->placeOrder($request, $cart_items)) {
            return response()->json([
                'status'    => true,
            ], 201);
        } else {
            return response()->json([
                'status'    => false,
                'errors'    => ['Something went wrong.']
            ], 500);
        }
    }


    public function orderList()
    {
        $order = $this->service->getUserOrder(auth()->user()->id);

        return response()->json([
            'status' => true,
            'data'  => $order
        ], count($order)==0 ? 204 : 200);
    }


    public function orderDetail($id)
    {
        $order = Cache::remember('customer_order_detail'.$id, 24*60*60, function () use ($id) {
            return $this->service->getData($id);
        });

        if($order && $order->user->id != auth()->guard('user-api')->user()->id) {
            return response()->json([
                'status'    => false,
                'errors'    => ['You are not authorized to fetch this order data.'],
            ], 401);
        }

        return response()->json([
            'status' => true,
            'data'   => $order,
        ], is_null($order) ? 204 : 200);
    }


    public function postReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review'            => 'required|string',
            'rating'            => 'required|integer|max:5|min:1',
            'order_item_id'     =>  ['required',
                                    function($attr, $val, $fail) {
                                        $item = OrderItems::find($val);

                                        if(is_null($item)) {
                                            $fail('Selected order item is invalid.');
                                        }

                                        else if($item->order->user_id != auth()->guard('user-api')->user()->id) {
                                            $fail('You must order the product to give a review.');
                                        }
                                        else if($item->order->order_status_id != 4) {
                                            $fail('You can not review a product unless it is delivered.');
                                        } else if(ProductReview::where('order_item_id',$val)->exists()) {
                                            $fail('You have already posted a review for this product.');
                                        }
                                    }
                                ],
            'multiple_image'    => 'nullable|array|min:1',
            'multiple_image.*'  => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $review = ProductReview::create([
                'order_item_id'         => $request->order_item_id,
                'review'                => $request->review,
                'rating'                => $request->rating
            ]);

            if ($request->multiple_image && count($request->multiple_image) > 0)
            {
                $this->saveMultipleImage($request->multiple_image, $review->id);
            }

            $review->orderItem->is_reviewed = 1;
            $review->orderItem->save();

            DB::commit();

            return response()->json([
                'status' => true,
            ], 201);
        } catch (QueryException $ex) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.'],
            ], 500);
        }
    }

    private function saveMultipleImage($images, $id): void
    {
        foreach ($images as $multiple_image)
        {
            $name = hexdec(uniqid()).'.'.$multiple_image->getClientOriginalExtension();
            $m_image = Image::make($multiple_image);
            $m_image->resize(500, 500);
            $m_image->save(public_path('/uploads/reviews/' . $name));

            ProductReviewImage::create([
                'product_review_id'     => $id,
                'image'                 => '/uploads/reviews/'.$name,
            ]);
        }
    }


    private function validatePromoCode($promo)
    {
        if($promo->is_active == 1) {
            if(!Carbon::parse($promo->start_date)->lessThanOrEqualTo(Carbon::today())) {
                return false;
            }
            if(!is_null($promo->end_date) && !Carbon::parse($promo->end_date)->greaterThanOrEqualTo(Carbon::today())) {
                return false;
            }
            if($promo->is_global_user == 0) {
                $valid_user = PromoUser::where('user_id', auth()->user()->id)->where('promo_id', $promo->id)->first();

                if(is_null($valid_user)) {
                    return false;
                }

                if($promo->max_usage!=0 && $promo->max_usage <= $valid_user->usage_number)  {
                    return false;
                }
            }

            if($promo->max_num_users!=0 && $promo->max_num_users == PromoUser::where('promo_id', $promo->id)->count()) {
                return false;
            }

            if($promo->is_global_product == 0) {
                $products = PromoProduct::where('promo_id',$promo->id)->select('product_id')->get();
                $cart_products = DB::table('customer_carts')
                    ->leftJoin('product_combinations','customer_carts.product_combination_id','=','product_combinations.id')
                    ->where('user_id',auth()->user()->id)->select('product_combinations.product_id as product_id')->get();

                $matches = collect($products)->pluck('product_id')->intersect(collect($cart_products)->pluck('product_id'));

                if ($matches->isEmpty())
                {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function cancelOrder($id)
    {
        $order = Order::where('user_id', auth()->guard('user-api')->user()->id)
            ->where('id',$id)->first();

        if (!$order)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Order not found.']
            ], 404);
        }

        if (!request()->input('reason') ||
                !in_array(\request()->input('reason'),
                    ['DELIVERY_ETA_TOO_LONG','MISTAKE_ERROR','REASON_UNKNOWN']))
        {
            return response()->json([
                'status' => false,
                'errors' => ['Please state a valid reason for cancellation.']
            ], 422);
        }

        $msg = (new OrderDeliverySystemService())->cancelOrder($order);

        if($msg != 'done')
        {
            return response()->json([
                'status'        => false,
                'errors'        => [$msg]
            ], 400);
        }

        return response()->json([
            'status'        => true,
        ]);
    }

    public function getPromos()
    {
        $data = (new PromoCodeService(new PromoCode()))->getUserPromos(auth()->user()->id);

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }
}
