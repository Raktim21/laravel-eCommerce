<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'location_countries';

    protected $hidden = ['created_at','updated_at'];

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}
