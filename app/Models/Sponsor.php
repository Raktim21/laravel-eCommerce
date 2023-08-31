<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Sponsor extends Model
{
    use HasFactory;

    protected $table = 'site_sponsors';

    protected $fillable = ['name','image','url'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($sponsor) {
            Cache::delete('sponsors');
        });

        static::updated(function ($sponsor) {
            Cache::delete('sponsors');
        });

        static::deleted(function ($sponsor) {
            Cache::delete('sponsors');
        });
    }
}
