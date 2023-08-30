<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class UserAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_addresses';

    protected $fillable = ['user_id','address','phone_no','upazila_id','union_id','postal_code','is_default','is_active'];

    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class,'upazila_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_address_id');
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function isDefault()
    {
        return $this->is_default == 1;
    }

    public function makeDefault()
    {
        $this->user->addresses()->update(['is_default' => 0]);
        $this->update(['is_default' => 1]);
    }

    public function remove()
    {
        $this->delete();
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($address) {
            Cache::delete('userAddresses'.$address->user_id);
        });

        static::updated(function ($address) {
            Cache::delete('userAddresses'.$address->user_id);
        });

        static::deleted(function ($address) {
            Cache::delete('userAddresses'.$address->user_id);
        });
    }
}
