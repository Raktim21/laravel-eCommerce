<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Subscriber extends Model
{
    use HasFactory;

    protected $table = 'subscribers';

    protected $guarded = ['id'];

    protected $hidden = ['updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($subscriber) {
            forgetCaches('subscriberList');
        });

        static::updated(function ($subscriber) {
            forgetCaches('subscriberList');
        });
    }
}
