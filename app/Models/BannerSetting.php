<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BannerSetting extends Model
{
    use HasFactory;

    protected $table = 'site_banner_settings';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($banner) {
            Cache::delete('allBanner');
            Cache::delete('banners');
        });

        static::updated(function ($banner) {
            Cache::delete('allBanner');
            Cache::delete('banners');
            Cache::delete('bannerSettingDetail'.$banner->id);
        });

        static::deleted(function ($banner) {
            Cache::delete('allBanner');
            Cache::delete('banners');
            Cache::delete('bannerSettingDetail'.$banner->id);
        });
    }
}
