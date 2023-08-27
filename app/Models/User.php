<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'shop_branch_id','username','password','salt','name','email_verified_at','phone_verified_at',
        'phone','password_reset_code','password_reset_token','last_login','remember_token',
        'google_id','facebook_id'
    ];

    protected $hidden = [
        'password','remember_token','salt','password_reset_code','password_reset_token',
        'updated_at','email_verified_at','phone_verified_at','deleted_at','google_id','facebook_id'
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

    public function emailVerificationCode()
    {
        return $this->hasOne(EmailVerificationCode::class, 'user_id');
    }

    public function scopeSearch($query)
    {
        $search = request()->search;
        return $query->when($search, function ($q) use ($search) {
            return $q->where('name', 'like', "%$search%")->orWhere('username', 'like', "%$search%")->orWhere('phone', 'like', "%$search%");
        });
    }
}
