<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeEventType extends Model
{
    use HasFactory;

    protected $table = 'theme_event_types';

    protected $guarded = ['id'];
}
