<?php

namespace App\Observers;

use App\Models\ProductCombination;

class ProductCombinationObserver
{
    public function updated(ProductCombination $combo)
    {
        if($combo->is_default == 1)
        {
            $combo->product->display_price = $combo->selling_price;
            $combo->product->save();
        }
    }
}
