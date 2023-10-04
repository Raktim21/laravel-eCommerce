<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $table = 'site_general_settings';

    protected $guarded = ['id'];

    protected $hidden = ['facebook_page_id','created_at','updated_at'];

    public function language()
    {
        return $this->belongsTo(DashboardLanguage::class, 'dashboard_language_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($setting) {
            Cache::delete('generalSetting');
            Cache::delete('general');
        });

        static::updated(function ($setting) {
            Cache::delete('generalSetting');
            Cache::delete('general');
        });
    }
}
