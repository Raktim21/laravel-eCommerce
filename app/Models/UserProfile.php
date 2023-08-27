<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';

    protected $fillable = ['user_id','user_sex_id','image','messenger_psid'];

    protected $hidden = ['messenger_psid','created_at','updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gender()
    {
        return $this->belongsTo(UserSex::class, 'user_sex_id');
    }

}
