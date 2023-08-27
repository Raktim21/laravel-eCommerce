<?php

namespace App\Observers;

use App\Models\FlashSale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class FlashSaleObserver
{
    public function updated(FlashSale $sale)
    {
        if($sale->status == 0 && $sale->end_date <= Carbon::now('Asia/Dhaka'))
        {
            Product::where('is_on_sale', 1)->update(['is_on_sale' => 0]);
        }

        Cache::delete('flash_sale');
    }
}
