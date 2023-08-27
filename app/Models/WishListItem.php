<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishListItem extends Model
{
    use HasFactory;

    protected $table = 'wishlist_items';

    protected $fillable = ['wishlist_id','product_combination_id'];
    protected $hidden = ['created_at','updated_at'];

    public function wish()
    {
        return $this->belongsTo(Wishlist::class, 'wishlist_id');
    }

    public function productCombination()
    {
        return $this->belongsTo(ProductCombination::class, 'product_combination_id');
    }
}
