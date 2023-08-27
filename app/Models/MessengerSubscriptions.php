<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessengerSubscriptions extends Model
{
    use HasFactory;

    protected $table = 'messenger_subscriptions';

    protected $fillable = ['user_id','subscription_type_id'];

    protected $hidden = ['created_at','updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
