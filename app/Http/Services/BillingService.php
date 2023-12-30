<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\Inventory;
use App\Models\OrderItems;
use App\Models\BillingCart;
use Illuminate\Http\Request;
use App\Models\BillingCartItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class BillingService
{

    protected $bill;

    public function __construct(BillingCart $bill)
    {
        $this->bill = $bill;
    }

    public function getCart()
    {
        return $this->bill->clone()->with('guest')
            ->with(['user' => function($q) {
                return $q->select('id','username','name','phone')->withTrashed();
            }])
            ->with(['items' => function($q) {
                return $q->with(['combinations' => function($q1) {
                    $q1->select('id','product_id','selling_price','weight','deleted_at')
                        ->with(['product' => function($q2) {
                            return $q2->select('id','name')->withTrashed();
                        }])->with(['attributeValues' => function($q3) {
                            return $q3->with(['attribute' => function($q4) {
                                return $q4->withTrashed();
                            }])->withTrashed();
                        }])->withTrashed();
                }]);
            }])
        ->latest()
        ->paginate(10);
    }

    public function getData($id)
    {
        return $this->bill->clone()->with('guest')
            ->with(['user' => function($q) {
                return $q->withTrashed();
            }])
            ->with(['items' => function($q) {
                return $q->with(['combinations' => function($q1) {
                    $q1->select('id','product_id','selling_price','weight')->withTrashed()
                        ->with(['product' => function($q2) {
                            return $q2->select('id','name')->withTrashed();
                        }])->with(['attributeValues' => function($q2) {
                            return $q2->withTrashed()
                            ->with(['attribute' => function($q3) {
                                return $q3->withTrashed();
                            }]);
                        }]);
                }]);
            }])->find($id);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $bill_cart = $this->bill->clone()->create([
                'billing_number'  => uniqid('BILL-'),
                'user_id'         => $request->user_id ?? null,
                'discount_amount' => $request->discount_amount ?? 0.00,
                'is_follow_up'    => $request->is_follow_up ?? 0,
                'remarks'         => $request->remarks
            ]);

            foreach ($request->product_combinations as $item)
            {
                BillingCartItems::create([
                    'billing_cart_id' => $bill_cart->id,
                    'product_combination_id' => $item['combination_id'],
                    'product_quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return $bill_cart->id;
        }
        catch (QueryException $ex)
        {
            DB::rollback();
            return 0;
        }
    }


    public function convert(Request $request, $id)
    {
        $bill = $this->bill->clone()->with('items')->findOrFail($id);

        if ($bill->is_ordered == 1)
        {
            return 1;
        }

        if (!$bill->user_id && !$request->user_id)
        {
            return 2;
        }

        if(!$this->checkEligibility($bill))
        {
            return 4;
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'shop_branch_id'             => auth()->guard('admin-api')->user()->shop_branch_id,
                'user_id'                    => $bill->user_id ?? $request->user_id,
                'order_number'               => 'ORD-' . implode('-', str_split(hexdec(uniqid()), 4)),
                'order_status_id'            => $request->delivery_method_id == 2 ? 4 : 2,
                'order_status_updated_by'    => auth()->user()->id,
                'payment_method_id'          => 1,
                'delivery_method_id'         => $request->delivery_method_id,
                'delivery_system_id'         => $request->delivery_method_id == 1 ? (new AssetService())->activeDeliverySystem() : null,
                'delivery_address_id'        => $request->delivery_method_id == 2 ? null : $request->delivery_address_id,
                'payment_status_id'          => $request->delivery_method_id == 2 ? 2 : 1,
                'delivery_status'            => $request->delivery_method_id == 2 ? 'Delivered' : 'Not Picked Yet'
            ]);

            $total = 0;
            $weight = 0;

            foreach ($bill->items as $item)
            {
                OrderItems::create([
                    'order_id'                  => $order->id,
                    'product_combination_id'    => $item->product_combination_id,
                    'product_quantity'          => $item->product_quantity,
                    'product_price'             => $item->combinations->selling_price,
                    'total_price'               => $item->product_quantity * $item->combinations->selling_price
                ]);

                $weight += $item->product_quantity * $item->combinations->weight;
                $total += $item->product_quantity * $item->combinations->selling_price;
            }

            $order->update([
                'promo_discount'    => ($bill->discount_amount * $total) / 100,
                'sub_total_amount'  => $total,
            ]);

            $bill->update([
                'is_ordered' => 1
            ]);

            if ($request->delivery_method_id == 1)
            {
                $delivery_system = (new AssetService())->activeDeliverySystem();

                if ($delivery_system == 2)
                {
                    if ($weight > 1)
                    {
                        DB::rollback();
                        return 6;
                    }

                    (new OrderDeliverySystemService())->eCourierOrder($order);
                }
                else if ($delivery_system == 3)
                {
                    (new OrderDeliverySystemService())->pandaGoOrder($order);
                }
            }

            DB::commit();

            return 3;
        }
        catch (QueryException $ex)
        {
            DB::rollback();

            return 5;
        }
    }

    private function checkEligibility($bill): bool
    {
        foreach ($bill->items as $bill_item)
        {
            $stock = Inventory::where('shop_branch_id', auth()->guard('admin-api')->user()->shop_branch_id)
                ->where('product_combination_id', $bill_item->product_combination_id)
                ->where('stock_quantity','>=',$bill_item->product_quantity)
                ->withTrashed()
                ->first();

            if(is_null($stock))
            {
                return false;
            }
        }
        return true;
    }

}
