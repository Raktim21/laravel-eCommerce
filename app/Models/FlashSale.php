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

    public static function boot()
    {
        parent::boot();

        static::created(function ($sale) {
            Cache::delete('flash_sale');
            Cache::delete('flashSale');
            Cache::delete('productOnSale');
        });

        static::updated(function ($sale) {
            Cache::delete('flash_sale');
            Cache::delete('flashSale');
            Cache::delete('productOnSale');
            if ($sale->status == 0)
            {
                Product::where('is_on_sale', 1)->update(['is_on_sale' => 0]);
            }
        });
    }
}
