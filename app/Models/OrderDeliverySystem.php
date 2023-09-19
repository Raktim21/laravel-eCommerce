<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliverySystem extends Model
{
    use HasFactory;

    protected $guarded = ['id','title','detail'];

    protected $hidden = ['created_at', 'updated_at'];
}
