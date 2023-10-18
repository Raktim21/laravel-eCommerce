<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $table = 'location_divisions';

    protected $hidden = ['local_name', 'url', 'created_at','updated_at'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function districts()
    {
        return $this->hasMany(Districts::class);
    }

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}
