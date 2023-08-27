<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCustomer extends Model
{
    use HasFactory;

    protected $table = 'billing_cart_customers';

    protected $guarded = ['id'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function billing_cart()
    {
        return $this->hasMany(BillingCart::class, 'billing_cart_customers_id');
    }

}
