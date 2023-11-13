<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeCustomizer extends Model
{
    use HasFactory;

    protected $table = 'theme_customizers';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

}
