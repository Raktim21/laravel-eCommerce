<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'shop_branch_id','username','password','salt','name','email_verified_at','phone_verified_at',
        'phone','password_reset_code','password_reset_token','last_login','remember_token',
        'google_id','facebook_id','is_active'
    ];

    protected $hidden = [
        'password','remember_token','salt','password_reset_code','password_reset_token',
        'updated_at','phone_verified_at','deleted_at','google_id','facebook_id'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'refresh_token' => false
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'shop_branch_id');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function emailVerification()
    {
        return $this->hasOne(EmailVerification::class, 'user_id');
    }

    public function contactForms()
    {
        return $this->hasMany(Contact::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function orderStatusUpdates()
    {
        return $this->hasMany(Order::class, 'order_status_updated_by');
    }

    public function cart()
    {
        return $this->hasMany(CustomerCart::class, 'user_id');
    }

    public function promos()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_users', 'user_id');
    }

    public function billing_cart()
    {
        return $this->hasMany(BillingCart::class, 'user_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function requests()
    {
        return $this->hasMany(ProductRestockRequest::class, 'user_id');
    }

    public function messenger_subscriptions()
    {
        return $this->hasMany(MessengerSubscriptions::class, 'user_id');
    }

    public function shopReviews()
    {
        return $this->hasMany(ShopReviews::class, 'user_id');
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class, 'user_id');
    }

    public function productAbuseReports()
    {
        return $this->hasMany(ProductAbuseReport::class, 'user_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            if($user->shop_branch_id)
            {
                forgetCaches('adminList');
            } else {
                forgetCaches('userList');
            }
        });

        static::updated(function ($user) {
            if($user->shop_branch_id)
            {
                forgetCaches('adminList');
                Cache::delete('adminDetail'.$user->id);
            } else {
                forgetCaches('userList');
                Cache::delete('userDetail'.$user->id);
            }
        });

        static::deleted(function ($user) {
            if($user->shop_branch_id)
            {
                forgetCaches('adminList');
                Cache::delete('adminDetail'.$user->id);
            } else {
                forgetCaches('userList');
                Cache::delete('userDetail'.$user->id);
                Cache::delete('userAddresses'.$user->id);
            }
        });
    }
}
