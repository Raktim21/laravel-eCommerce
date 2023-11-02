<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OrderAdditionalCharge extends Model
{
    use HasFactory;

    protected $table = 'order_additional_charges';

    protected $fillable = ['name','amount','is_percentage','status'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($charge) {
            Cache::delete('orderAdditionalCharges0');
            Cache::delete('orderAdditionalCharges1');
            Cache::delete('additional_charges');
        });

        static::updated(function ($charge) {
            Cache::delete('orderAdditionalCharges0');
            Cache::delete('orderAdditionalCharges1');
            Cache::delete('additional_charges');
        });

        static::deleted(function ($charge) {
            Cache::delete('orderAdditionalCharges0');
            Cache::delete('orderAdditionalCharges1');
            Cache::delete('additional_charges');
        });
    }
}
