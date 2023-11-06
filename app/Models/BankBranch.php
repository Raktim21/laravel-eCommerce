<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankBranch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['routing_no','created_at','updated_at'];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function payment_info()
    {
        return $this->hasMany(MerchantPaymentInfo::class, 'bank_branch_id');
    }
}
