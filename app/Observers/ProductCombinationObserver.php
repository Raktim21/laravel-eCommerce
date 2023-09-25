<?php

namespace App\Observers;

use App\Models\ProductCombination;

class ProductCombinationObserver
{
    public function updated(ProductCombination $combo)
    {
        if($combo->is_default == 1)
        {
            if(!$combo->product->previous_display_price)
            {
                if($combo->selling_price < $combo->product->display_price)
                {
                    $combo->product->previous_display_price = $combo->product->display_price;
                }
            } else {
                if ($combo->product->previous_display_price < $combo->selling_price)
                {
                    $combo->product->previous_display_price = null;
                }
            }

            $combo->product->display_price = $combo->selling_price;

            $combo->product->save();
        }
    }
}
