<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSex extends Model
{
    use HasFactory;

    protected $table = 'user_sexes';

    protected $hidden = ['created_at','updated_at'];

    protected $guarded = ['id','name'];

    public function user()
    {
        return $this->hasMany(UserProfile::class, 'user_sex_id');
    }
}
