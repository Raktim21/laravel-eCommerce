<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use DB;

class ProductDetailsComponent extends Component
{
    public $slug;

    public function mount($slug){
        $this->slug=$slug;
    }
    public function render()
    {
        $details = DB::table('products')->where('slug',$this->slug)->first();
        $popular_products = DB::table('products')->inRandomOrder()->limit(4)->get();
        $related_products = DB::table('products')->where('category_id',$details->category_id)->limit(8)->get();

        return view('livewire.product-details-component',compact('details','popular_products','related_products'))->layout('layouts.base');
    }
}
