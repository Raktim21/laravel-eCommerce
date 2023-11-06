<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OrderDeliverySystem extends Model
{
    use HasFactory;

    protected $guarded = ['id','title','detail'];

    protected $hidden = ['created_at', 'updated_at'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_system_id');
    }

    public function payment_info()
    {
        return $this->hasOne(MerchantPaymentInfo::class, 'delivery_system_id');
    }

    public static function boot()
    {
        parent::boot();

        static::updated(function ($system) {
            Cache::delete('orderStatuses');
        });
    }
}
