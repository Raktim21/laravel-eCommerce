<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartComponent extends Component
{
    public function increaseItem($rowId){

        $product = Cart::get($rowId);
        $qty = $product->qty + 1;
        Cart::update($rowId,$qty);

    }

    public function decreaseItem($rowId){

        $product = Cart::get($rowId);
        $qty = $product->qty - 1;
        Cart::update($rowId,$qty);

    }

    public function destroyItem($rowId){

        Cart::remove($rowId);
        session()->flush('success','Item has been removed');
       

    }
    public function render()
    {
        return view('livewire.cart-component')->layout('layouts.base');
    }
}
