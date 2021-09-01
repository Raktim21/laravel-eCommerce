<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Gloudemans\Shoppingcart\Facades\Cart;
// use Cart;

class ShopComponent extends Component
{
    public function store($product_id){

        $product = DB::table('products')->where('id',$product_id)->first();
     
        Cart::add(
            $product->id,
            $product->name,
            1,
            $product->regular_price,
          

            )->associate('App\Models\Product');

       
        return redirect()->route('home.cart');
           
    }

    use WithPagination;
    public function render()
    {
        $products = Product::paginate(12);
        return view('livewire.shop-component',compact('products'))->layout('layouts.base');
    }
}
