<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteBanners extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($banner) {
            Cache::delete('siteBanners');
            Cache::delete('site_banners');
        });

        static::updated(function ($banner) {
            Cache::delete('siteBanners');
            Cache::delete('site_banners');
        });
    }
}
