<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantPaymentMethods extends Model
{
    use HasFactory;

    public function payment_info()
    {
        return $this->hasMany(MerchantPaymentInfo::class, 'payment_method_id');
    }
}
