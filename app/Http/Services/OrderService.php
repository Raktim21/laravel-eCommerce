<?php

namespace App\Http\Services;

use App\Models\FlashSale;
use App\Models\GeneralSetting;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderAdditionalCharge;
use App\Models\OrderItems;
use App\Models\OrderPickupAddress;
use App\Models\PickupAddress;
use App\Models\ProductCombination;
use App\Models\ProductHasPromo;
use App\Models\PromoCode;
use App\Models\PromoProduct;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAddress;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderService
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function placePOSOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $new_order = $this->order->newQuery()->create([
                'shop_branch_id'             => auth()->guard('admin-api')->user()->shop_branch_id,
                'user_id'               => $request->user_id,
                'order_number'          => 'ORD-' . implode('-', str_split(hexdec(uniqid()), 4)),
                'order_status_id'       => $request->delivery_method_id == 2 ? 4 : 2,
                'order_status_updated_by' => auth()->guard('admin-api')->user()->id,
                'payment_method_id'     => 1,
                'delivery_method_id'    => $request->delivery_method_id,
                'delivery_address_id'   => $request->delivery_address_id ?? null,
                'delivery_status'       => $request->delivery_method_id == 2 ? 'Delivered' : 'Not Picked Yet',
                'merchant_remarks'      => $request->merchant_remarks,
                'payment_status_id'     => $request->delivery_method_id == 2 ? 2 : 1,
            ]);

            $total = 0;
            $weight = 0;

            foreach ($request->product_combinations as $item)
            {
                $combo = ProductCombination::find($item['combination_id']);

                OrderItems::create([
                    'order_id'                  => $new_order->id,
                    'product_combination_id'    => $item['combination_id'],
                    'product_quantity'          => $item['quantity'],
                    'product_price'             => $combo->selling_price,
                    'total_price'               => $combo->selling_price * $item['quantity']
                ]);

                $total  += $combo->selling_price * $item['quantity'];
                $weight += $combo->weight * $item['quantity'];
            }

            if($request->delivery_method_id == 1 && $weight > 5) {
                return 2;
            }

            $new_order->update([
                'sub_total_amount'      => $total,
                'promo_discount'        => ($request->promo_discount * $total)/100,
            ]);

            if($request->delivery_method_id == 1 && (new GeneralSettingService(new GeneralSetting()))->getSetting()->delivery_status == 1) {
                $this->paperFlyOrder($new_order, $weight);
            }

            DB::commit();
            return $new_order->order_number;
        }
        catch (QueryException $ex) {
            DB::rollback();
            return 1;
        }
    }

    public function getData($id)
    {
        return Order::with('items.reviews','paymentMethod','status',
            'deliveryMethod')
            ->with(['deliveryAddress' => function($q) {
                return $q->withTrashed()->with('upazila.district.division.country','union');
            }])
            ->with(['items.combination' => function($q) {
                return $q->with(['product' => function($q1) {
                    return $q1->select('id','name','thumbnail_image')->withTrashed();
                }])->with(['attributeValues' => function($q1) {
                    return $q1->withTrashed();
                }])->withTrashed();
            }])
            ->with('promoCode')
            ->with(['user' => function($q) {
                return $q->select('id','name','username')->withTrashed();
            }])->find($id);
    }

    public function getOrderList($addedByAdmin)
    {
        return $this->order->newQuery()
            ->when($addedByAdmin==true, function ($q) {
                return $q->where('delivery_method_id', 2);
            })
            ->when($addedByAdmin==false, function ($q) {
                return $q->whereNot('delivery_method_id',2);
            })
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('user_addresses', 'orders.delivery_address_id', '=', 'user_addresses.id')
            ->leftJoin('order_payment_statuses', 'orders.payment_status_id', '=', 'order_payment_statuses.id')
            ->leftJoin('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
            ->select('orders.id as order_id','orders.shop_branch_id as shop_branch_id','orders.order_number',
                'orders.delivery_tracking_number','orders.delivery_status','orders.order_status_id','order_statuses.name as order_status',
                'orders.total_amount','users.name','user_addresses.phone_no as shipping_phone',
                'order_payment_statuses.name as payment_status','orders.created_at')
            ->when(request()->input('order_number'), function ($q) {
                return $q->where('order_number', request()->input('order_number'));
            })
            ->when(request()->input('tracking_number'), function ($q) {
                return $q->where('delivery_tracking_number', request()->input('tracking_number'));
            })
            ->when(request()->input('customer_name'), function ($q) {
                return $q->where('users.name', 'like', '%'.request()->input('customer_name').'%');
            })
            ->when(request()->input('customer_phone'), function ($q) {
                return $q->where('user_addresses.phone_no', request()->input('customer_phone'));
            })
            ->when(request()->input('delivery_status'), function ($q) {
                return $q->where('delivery_status', request()->input('delivery_status'));
            })
            ->when(request()->input('order_status'), function ($q) {
                return $q->where('order_status_id', request()->input('order_status'));
            })
            ->when(request()->input('start_date') && request()->input('end_date'), function ($q) {
                return $q->whereBetween('orders.created_at', [request()->input('start_date'), request()->input('end_date')]);
            })
            ->orderBy('orders.id', 'DESC')
            ->paginate(15)->appends(request()->except('page'));
    }

    public function paperFlyOrder($order, $weight): void
    {
        $client = new Client();
        $pickup = OrderPickupAddress::first();
        if(!is_null($pickup))
        {
            $name = '';
            foreach($order->items as $item){
                $name .= $item->combination->product->category->name.'('.$item->quantity.')';
            }
            $response = $client->post(peperfly()['paperFlyUrl'].'/OrderPlacement', [
                'headers' => [
                    'paperflykey' =>  peperfly()['paperFlyKey']
                ],
                'auth' =>  peperfly()['credential'],
                'json' => [
                    "merOrderRef"          => $order->order_number,
                    "pickMerchantName"     => $pickup->name,
                    "pickMerchantAddress"  => $pickup->address,
                    "pickMerchantThana"    => $pickup->upazila->name,
                    "pickMerchantDistrict" => $pickup->upazila->district->name,
                    "pickupMerchantPhone"  => $pickup->phone,
                    "productSizeWeight"    => "standard",
                    "productBrief"         => $name,
                    "packagePrice"         => $order->total_amount + 2,
                    "max_weight"           => $weight.'kg',
                    "deliveryOption"       => "regular",
                    "custname"             => $order->user->name,
                    "custaddress"          => $order->deliveryAddress->address,
                    "customerThana"        => $order->deliveryAddress->upazila->name,
                    "customerDistrict"     => $order->deliveryAddress->upazila->district->name,
                    "custPhone"            => $order->deliveryAddress->phone_no,
                ],
            ]);

            if ($response->getStatusCode() == 200) {

                $data = json_decode($response->getBody());
                $order->delivery_tracking_number = $data->success->tracking_number;
                $order->save();
            }
        }
    }

    public function getOrderWeight($order): float|int
    {
        $weight = 0;

        foreach ($order->items()->get() as $item)
        {
            $weight += $item->combination->weight * $item['product_quantity'];
        }

        return $weight;
    }

    public function paperFlyCancelOrder($order): void
    {
        $client = new Client();

        $response = $client->post(peperfly()['paperFlyUrl'] . '/api/v1/cancel-order/', [
            'headers' => [
                'paperflykey' =>  peperfly()['paperFlyKey']
            ],
            'auth' => peperfly()['credential'],
            'json' => [
                "order_id" => $order->order_number,
            ],
        ]);

        json_decode($response->getBody()->getContents(), true);

        $order->delivery_tracking_number = null;
        $order->save();
    }

    public function placeOrder(Request $request, $cart_items, $weight)
    {
        DB::beginTransaction();

        try {
            $new_order = Order::create([
                'user_id'                   => auth()->user()->id,
                'order_number'              => 'ORD-' . implode('-', str_split(hexdec(uniqid()), 4)),
                'payment_method_id'         => $request->payment_method_id,
                'delivery_method_id'        => 1,
                'delivery_address_id'       => $request->delivery_address_id,
                'delivery_remarks'          => $request->delivery_remarks,
                'delivery_status'           => 'Not Picked Yet',
                'promo_code_id'             => $request->promo_code_id ?? null,
                'payment_status_id'         => 1
            ]);

            $total = 0;
            $discount = 0;

            foreach ($cart_items as $cart_item) {
                $total += $cart_item->productCombination->selling_price * $cart_item->product_quantity;

                $item_total = $cart_item->productCombination->selling_price * $cart_item->product_quantity;

                OrderItems::create([
                    'order_id'                      => $new_order->id,
                    'product_combination_id'        => $cart_item['product_combination_id'],
                    'product_quantity'              => $cart_item['product_quantity'],
                    'product_price'                 => $cart_item->productCombination->selling_price,
                    'total_price'                   => $item_total
                ]);

                if(!is_null($request->promo_code_id) && $cart_item->productCombination->product->is_on_sale == 0) {
                    $promo = PromoCode::find($request->promo_code_id);

                    if($promo->is_global_product == 0) {
                        $promo_exist = PromoProduct::where('promo_id', $promo->id)
                            ->where('product_id', $cart_item->productCombination->product_id)->first();

                        if($promo_exist) {
                            $discount += $promo->is_percentage==1 ? (($item_total * $promo->discount)/100) : ($promo->discount * $cart_item['product_quantity']);
                        }
                    } else {
                        $discount += $promo->is_percentage==1 ? (($item_total * $promo->discount)/100) : ($promo->discount * $cart_item['product_quantity']);
                    }
                }
                else if ($cart_item->productCombination->product->is_on_sale == 1)
                {
                    $sale = FlashSale::first();

                    if($sale && $sale->status == 1 && Carbon::parse($sale->end_date)->gte(Carbon::now()))
                    {
                        $discount += ($item_total * $sale->discount)/100;
                    }
                }
            }

            $new_order->update([
                'sub_total_amount'      => $total,
                'promo_discount'        => $discount,
            ]);

            DB::commit();

            Cache::delete('admin_dashboard_data');

            return true;
        } catch(QueryException $e) {
            DB::rollback();
            return false;
        }
    }


    public function placeMessengerOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $promo = $request->has('promo_code') ? PromoCode::where('code', $request->promo_code)->first() : null;

            $user = User::firstOrCreate(
                ['username' => $request->email],
                [
                    'name'                  => $request->name,
                    'phone'                 => $request->phone_no,
                    'password'              => Hash::make($request->password),
                ]);

            $user->is_active = 1;
            $user->save();

            \Config::set('auth.defaults.guard','user-api');
            $user->assignRole(3);
            \Config::set('auth.defaults.guard','');

            UserProfile::firstOrCreate([
                'user_id'   => $user->id,
            ],[
                'user_sex_id' => $request->user_sex_id,
                'messenger_psid' => $request->messenger_psid
            ]);

            $address = UserAddress::firstOrCreate([
                'user_id'                   => $user->id,
                'upazila_id'                => $request->upazila_id,
                'union_id'                  => $request->union_id,
                'address'                   => $request->address,
                'phone_no'                  => $request->phone_no
            ],[
                'user_id'                   => $user->id,
                'upazila_id'                => $request->upazila_id,
                'union_id'                  => $request->union_id,
                'address'                   => $request->address,
                'phone_no'                  => $request->phone_no
            ]);

            $new_order = Order::create([
                'user_id'                   => $user->id,
                'order_number'              => 'ORD-' . implode('-', str_split(hexdec(uniqid()), 4)),
                'payment_method_id'         => $request->payment_method_id,
                'delivery_method_id'        => $request->delivery_method_id,
                'delivery_address_id'       => $address->id,
                'delivery_remarks'          => $request->delivery_notes,
                'delivery_status'           => 'Not Picked Yet',
                'promo_code_id'             => is_null($promo) ? null : $promo->id,
                'payment_status_id'         => 1
            ]);

            $total = 0;
            $discount = 0;
            $weight = 0;

            foreach($request->order_items as $item)
            {
                $combo = ProductCombination::find($item['product_attribute_combination_id']);
                $total += $combo->selling_price * $item['product_quantity'];
                $item_total = $combo->selling_price * $item['product_quantity'];
                $weight += $combo->weight;

                OrderItems::create([
                    'order_id'                  => $new_order->id,
                    'product_combination_id'    => $item['product_attribute_combination_id'],
                    'product_quantity'          => $item['product_quantity'],
                    'product_price'             => $combo->selling_price,
                    'total_price'               => $item_total
                ]);

                if(!is_null($promo))
                {
                    if($promo->is_global_product == 0) {
                        $promo_exist = PromoProduct::where('promo_id', $promo->id)
                            ->where('product_id', $combo->product_id)->first();

                        if($promo_exist) {
                            $discount += $promo->is_percentage==1 ? (($item_total * $promo->discount)/100) : ($promo->discount * $item['product_quantity']);
                        }
                    } else {
                        $discount += $promo->is_percentage==1 ? (($item_total * $promo->discount)/100) : ($promo->discount * $item['product_quantity']);
                    }
                }
            }

            $new_order->update([
                'sub_total_amount'      => $total,
                'promo_discount'        => $discount,
            ]);

            DB::commit();

            Cache::delete('admin_dashboard_data');

            return 'done';
        } catch (QueryException $ex)
        {
            DB::rollback();
            return $ex->getMessage();
        }
    }

    public function getCharges()
    {
        return OrderAdditionalCharge::all();
    }

    public function storeOrderCharges(Request $request)
    {
        OrderAdditionalCharge::create([
            'name'          => $request->name,
            'amount'        => $request->amount,
            'is_percentage'    => $request->is_percentage,
        ]);
    }

    public function updateOrderCharge(Request $request, $id)
    {
        OrderAdditionalCharge::findOrFail($id)->update([
            'name'          => $request->name,
            'amount'        => $request->amount,
            'is_percentage'    => $request->is_percentage,
            'status'        => $request->status
        ]);
    }

    public function deleteOrderCharge($id): void
    {
        OrderAdditionalCharge::findOrFail($id)->delete();
    }

    public function checkEligibility($order, $branch): bool
    {
        foreach ($order->items as $order_item)
        {
            $stock = Inventory::where('shop_branch_id', $branch)
                ->where('product_combination_id', $order_item->product_combination_id)
                ->where('stock_quantity','>=',$order_item->product_quantity)
                ->withTrashed()
                ->first();

            if(is_null($stock))
            {
                return false;
            }
        }
        return true;
    }

    public function cancelOrder($order, $user): string
    {
        if($order->user_id != $user)
        {
            return 'Selected order is invalid.';
        }
        if($order->delivery_status == 'Picked')
        {
            return 'You cannot cancel order after being picked.';
        }
        if($order->delivery_status == 'Delivered' || $order->order_status_id == 4)
        {
            return 'You cannot cancel order after being delivered.';
        }
        if($order->delivery_tracking_number != null)
        {
            $this->paperFlyCancelOrder($order);
        }
        $order->delivery_status = 'Cancelled';
        $order->order_status_id = 3;
        $order->save();
        return 'done';
    }

}
