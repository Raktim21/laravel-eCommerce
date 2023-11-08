<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantPaymentInfo extends Model
{
    use HasFactory;

    public function delivery_system()
    {
        return $this->belongsTo(OrderDeliverySystem::class, 'delivery_system_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(MerchantPaymentMethods::class, 'payment_method_id');
    }

    public function bank_branch()
    {
        return $this->belongsTo(BankBranch::class, 'bank_branch_id');
    }
}
