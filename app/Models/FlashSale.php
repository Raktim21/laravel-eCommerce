<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FlashSale extends Model
{
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'status'    => 'boolean'
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($sale) {
            Cache::delete('flash_sale');
            Cache::delete('flashSale');
            Cache::delete('productOnSale');
            Cache::delete('productDiscount');
        });

        static::updated(function ($sale) {
            Cache::delete('productDiscount');
            Cache::delete('flash_sale');
            Cache::delete('flashSale');
            Cache::delete('productOnSale');
            Cache::delete('flashSaleStatus');
        });
    }
}
