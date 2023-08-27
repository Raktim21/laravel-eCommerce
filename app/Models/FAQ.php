<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory;

    protected $table = 'static_faqs';

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];
}
