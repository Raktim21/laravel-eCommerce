<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeLog extends Model
{
    use HasFactory;

    protected $table = 'theme_logs';

    protected $guarded = ['id'];
}
