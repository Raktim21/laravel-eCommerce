<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PromoCode extends Model
{
    use HasFactory;

    protected $table = 'promo_codes';

    protected $fillable = ['title','code','is_active','is_global_user','is_global_product','is_percentage','discount','max_usage','max_num_users','start_date','end_date'];

    protected $hidden = ['created_at','updated_at'];

    protected $casts = [
        'is_active'         => 'boolean',
        'is_global_user'    => 'boolean',
        'is_global_product' => 'boolean',
        'is_percentage'     => 'boolean'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'promo_code_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promo_products', 'promo_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'promo_users', 'promo_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($code) {
            forgetCaches('promoCodeList');
            forgetCaches('customer_promo');
        });

        static::updated(function ($code) {
            forgetCaches('promoCodeList');
            Cache::delete('promoCodeDetail'.$code->id);
        });
    }
}
