<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OrderPickupAddress extends Model
{
    use HasFactory;

    protected $table = 'order_pickup_addresses';

    protected $guarded = ['id'];

    protected $hidden = ['pickup_unique_id','created_at','updated_at'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'shop_branch_id');
    }

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

        static::creating(function ($address) {
            $address->pickup_unique_id = Str::before(uuid_create(), '-');
        });

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
