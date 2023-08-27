<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $guarded = ['order_id','product_combination_id'];

    protected $fillable = ['order_id','product_combination_id','product_quantity',
        'product_price','total_price','is_reviewed'];

    protected $hidden = ['updated_at','created_at'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function combination()
    {
        return $this->belongsTo(ProductCombination::class, 'product_combination_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'order_item_id');
    }

}
