<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;

class ShopComponent extends Component
{
    public function render()
    {
        $products = Product::paginate(12);
        return view('livewire.shop-component',compact('products'))->layout('layouts.base');
    }
}
