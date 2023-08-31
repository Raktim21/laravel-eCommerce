<?php

namespace App\Observers;

use App\Http\Services\GeneralSettingService;
use App\Mail\OrdersMail;
use App\Models\CustomerCart;
use App\Models\GeneralSetting;
use App\Models\Inventory;
use App\Models\MessengerSubscriptions;
use App\Models\Order;
use App\Models\OrderAdditionalCharge;
use App\Models\PromoCode;
use App\Models\PromoUser;
use App\Models\User;
use App\Notifications\CustomerOrderDeliveryNotification;
use App\Notifications\OrderDeliveryNotification;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function creating(Order $order)
    {
        if($order->promo_code_id) {
            $promo = PromoCode::find($order->promo_code_id);

            $user_promo = PromoUser::where('promo_id', $promo->id)
                ->where('user_id', $order->user_id)->first();
            if(!is_null($user_promo)) {
                $user_promo->usage_number += 1;
                $user_promo->save();
            } else {
                PromoUser::create([
                    'user_id'   => $order->user_id,
                    'promo_id'  => $promo->id,
                ]);
            }
        }
    }


    public function created(Order $order)
    {
        if(is_null($order->shop_branch_id)) {

            if(request()->has('messenger_psid'))
            {
                $payload = array(
                    'psid'          => request()->input('messenger_psid'),
                    'page_id'       => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
                    'order_id'      => $order->order_number,
                    'invoice_link'  => 'order/invoice?order_id=' . Crypt::encrypt($order->id)
                );

                sendMessengerResponse($payload, 'order_confirmation');
            }

            $admins = User::whereHas('roles', function ($query) {
                    $query->whereIn('id', [1,2]);
                })->get();

//        notify admins about new order

            foreach ($admins as $admin) {
                $admin->notify(new OrderPlacedNotification($order));
            }

//        delete customer cart
            if(auth()->guard('user-api')->check())
            {
                CustomerCart::where('user_id',auth()->user()->id)->delete();
            }

//        email user
            $this->notifyUser($order->order_number);
        }
    }


    public function updating(Order $order)
    {
        if($order->total_amount == 0)
        {
            $tax = $this->calculateTax($order->sub_total_amount);
            $delivery_charge = $order->delivery_method_id == 1 ?
                getDeliveryCharge($order->delivery_address_id, $order->sub_total_amount + $tax - $order->promo_discount) : 0;

            $order->additional_charges          = $tax;
            $order->total_amount                = $order->sub_total_amount + $tax - $order->promo_discount + $delivery_charge;
            $order->delivery_cost               = $delivery_charge;
            $order->paid_amount                 = $order->delivery_method_id == 2 ?
                $order->sub_total_amount + $tax - $order->promo_discount + $order->delivery_cost : 0;
        }
    }


    public function updated(Order $order)
    {
        Cache::delete('orderDetail'.$order->id);
        if ($order->delivery_status == 'Delivered' || $order->delivery_status == 'Picked' || $order->delivery_status == 'Cancelled') {
            if ($order->delivery_status == 'Delivered') {

                $admins = User::whereDoesntHave('roles', function ($query) {
                    $query->where('id', 3);
                })->get();

                foreach ($admins as $admin) {
                    $admin->notify(new OrderDeliveryNotification($order));
                }
            }
//            User::find($order->user_id)->notify(new CustomerOrderDeliveryNotification($order));

            $subscription = MessengerSubscriptions::where('user_id', $order->user_id)
                ->where('subscription_type_id', 2)->first();

            if(!is_null($subscription)) {
                $payload = array(
                    'psid'          => $subscription->user->profile->messenger_psid,
                    'page_id'       => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
                    'order_status'  => $order->delivery_status,
                    'order_no'      => $order->order_number
                );
                sendMessengerResponse($payload, 'order_status');
            }
        }

        if($order->order_status_id == 4)
        {
            foreach ($order->items()->get() as $item)
            {
                $stock = Inventory::where('shop_branch_id', $order->shop_branch_id)
                    ->where('product_combination_id', $item['product_combination_id'])->withTrashed()->first();

                $stock['stock_quantity'] -= $item['product_quantity'];
                $stock->save();

                $item->combination->product->sold_count += 1;
                $item->combination->product->save();
            }
        }
    }

    private function calculateTax($total)
    {
        $taxes = OrderAdditionalCharge::where('status', 1)->get();

        $tax_total = 0;

        foreach ($taxes as $tax) {
            $tax_total += ($tax->is_percentage==1 ? (($tax->amount*$total)/100) : $tax->amount);
        }

        return $tax_total;
    }

    private function notifyUser($order_number): void
    {
        try {
            $to = auth()->user()->username;

            $message = "You have recently placed a new order. Your order number is {$order_number}. We will notify you shortly when the order is ready for shipment.";

            $mail_data = [
                'user' => auth()->user()->name,
                'body' => $message
            ];

            Mail::to($to)->queue(new OrdersMail($mail_data));
        } catch (\Throwable $th) {}
    }
}
