<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upazila extends Model
{
    use HasFactory;

    protected $table = 'location_upazilas';

    protected $hidden = ['created_at','updated_at'];

    public function district()
    {
        return $this->belongsTo(Districts::class);
    }

    public function unions()
    {
        return $this->hasMany(Union::class);
    }

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}
