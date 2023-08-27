<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Union extends Model
{
    use HasFactory;

    protected $table = 'location_unions';

    protected $hidden = ['created_at','updated_at'];

    public function subDistrict()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}
