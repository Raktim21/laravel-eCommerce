<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryChargeLookup extends Model
{
    use HasFactory;

    protected $fillable = ['amount'];

    protected $hidden = ['created_at', 'updated_at'];
}
