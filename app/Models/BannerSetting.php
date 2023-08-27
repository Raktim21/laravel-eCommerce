<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerSetting extends Model
{
    use HasFactory;

    protected $table = 'site_banner_settings';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];
}
