<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BillingCart extends Model
{
    use HasFactory;

    protected $table = 'billing_carts';

    protected $fillable = [
        'billing_number', 'user_id', 'billing_cart_customers_id',
        'discount_amount', 'remarks', 'is_follow_up', 'is_ordered'
    ];

    protected $hidden = ['updated_at'];

    public function items()
    {
        return $this->hasMany(BillingCartItems::class, 'billing_cart_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guest()
    {
        return $this->belongsTo(BillingCustomer::class, 'billing_cart_customers_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($bill) {
            forgetCaches('billingList');
        });

        static::updated(function ($bill) {
            Cache::delete('billDetail'.$bill->id);
            forgetCaches('billingList');
        });
    }
}
