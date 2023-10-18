<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Districts extends Model
{
    use HasFactory;

    protected $table = 'location_districts';

    protected $hidden = ['local_name', 'lat', 'lon', 'url', 'created_at','updated_at'];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function subDistricts()
    {
        return $this->hasMany(Upazila::class, 'district_id', 'id')->latest();
    }

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}
