<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'string',
        'is_send' => 'boolean'
    ];
}
