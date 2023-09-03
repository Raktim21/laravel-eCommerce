<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contact_forms';

    protected $guarded = ['id','user_id','guest_session_id','ip_address'];

    protected $hidden = ['updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($contact) {
            forgetCaches('contactList');
        });

        static::updated(function ($contact) {
            forgetCaches('contactList');
        });

        static::deleted(function ($contact) {
            forgetCaches('contactList');
        });
    }
}
