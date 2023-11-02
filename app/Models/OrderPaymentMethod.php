<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPaymentMethod extends Model
{
    use HasFactory;

    protected $hidden = ['created_at','updated_at'];

    protected $table = 'order_payment_methods';

    protected $casts = [
        'is_active'    => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_method_id');
    }
}
