<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $guarded = ['user_id','order_number'];

    protected $fillable = ['shop_branch_id','user_id','order_number','order_status_id','order_status_updated_by',
        'payment_method_id','delivery_method_id','delivery_system_id','delivery_address_id','delivery_tracking_number','delivery_cost',
        'delivery_status','delivery_remarks','merchant_remarks','promo_code_id','promo_discount',
        'additional_charges','total_amount','sub_total_amount','paid_amount','payment_status_id'];

    protected $hidden = ['delivery_system_id','updated_at'];


    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'shop_branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function statusUpdatedBy()
    {
        return $this->belongsTo(User::class, 'order_status_updated_by');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(OrderPaymentMethod::class, 'payment_method_id');
    }

    public function deliveryMethod()
    {
        return $this->belongsTo(OrderDeliveryMethod::class, 'delivery_method_id');
    }

    public function deliverySystem()
    {
        return $this->belongsTo(OrderDeliverySystem::class, 'delivery_system_id');
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(UserAddress::class, 'delivery_address_id');
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }

    public function paymentStatus()
    {
        return $this->belongsTo(OrderPaymentStatus::class, 'payment_status_id');
    }
}
