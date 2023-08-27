<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCartItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_cart_id',
        'product_combination_id',
        'product_quantity'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function billing_cart()
    {
        return $this->belongsTo(BillingCart::class, 'billing_cart_id');
    }

    public function combinations()
    {
        return $this->belongsTo(ProductCombination::class, 'product_combination_id');
    }
}
