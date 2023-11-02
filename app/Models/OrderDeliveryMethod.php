<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryMethod extends Model
{
    use HasFactory;

    protected $table = 'order_delivery_methods';

    protected $hidden = ['created_at','updated_at'];

    protected $casts = [
        'is_active'    => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_method_id');
    }
}
