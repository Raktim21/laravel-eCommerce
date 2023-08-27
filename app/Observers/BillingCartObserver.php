<?php

namespace App\Observers;

use App\Models\BillingCart;
use App\Models\BillingCustomer;

class BillingCartObserver
{
    public function created(BillingCart $cart)
    {
        if($cart->user_id==null)
        {
            $customer = BillingCustomer::updateOrCreate([
                'name'      => request()->input('customer_name')
            ],[
                'phone'     => request()->input('customer_phone'),
                'email'     => request()->input('customer_email')
            ]);

            $cart->billing_cart_customers_id = $customer->id;
            $cart->save();
        }
    }
}
