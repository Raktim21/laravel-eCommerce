<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    public static function boot()
    {
        parent::boot();

        static::updated(function ($profile) {
            if($profile->user->shop_branch_id)
            {
                Cache::delete('adminDetail'.$profile->user_id);
                Cache::delete('adminAuthProfile'.$profile->user_id);
            } else {
                Cache::delete('userDetail'.$profile->user_id);
                Cache::delete('customer_auth_profile'.$profile->user_id);
            }
        });
    }
}
