<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoProduct extends Model
{
    use HasFactory;

    protected $table = 'promo_products';

    protected $fillable = ['product_id','promo_id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function promo()
    {
        return $this->belongsTo(PromoCode::class, 'promo_id');
    }
}
