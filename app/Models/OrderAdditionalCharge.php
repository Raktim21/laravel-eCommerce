<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAdditionalCharge extends Model
{
    use HasFactory;

    protected $table = 'order_additional_charges';

    protected $fillable = ['name','amount','is_percentage','status'];

    protected $hidden = ['created_at','updated_at'];
}
