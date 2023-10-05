<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OrderPickupAddress extends Model
{
    use HasFactory;

    protected $table = 'order_pickup_addresses';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at','deleted_at'];

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($address) {
            Cache::delete('pickupAddress');
            Cache::delete('pickup_info');
        });

        static::updated(function ($address) {
            Cache::delete('pickupAddress');
            Cache::delete('pickup_info');
        });
    }
}
