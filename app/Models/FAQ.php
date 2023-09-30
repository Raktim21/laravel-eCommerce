<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FAQ extends Model
{
    use HasFactory;

    protected $table = 'static_faqs';

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($faq) {
            Cache::forget('faqs');
        });

        static::updated(function ($faq) {
            Cache::forget('faqs');
        });

        static::deleted(function ($faq) {
            Cache::forget('faqs');
        });
    }
}
