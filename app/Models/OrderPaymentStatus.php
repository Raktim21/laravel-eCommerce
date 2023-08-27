<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPaymentStatus extends Model
{
    use HasFactory;

    protected $table = 'order_payment_statuses';

    protected $guarded = ['id','name'];

    protected $hidden = ['created_at','updated_at'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_status_id');
    }
}
