<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;

    protected $table = 'email_verifications';

    protected $guarded = ['id'];
    
    protected $hidden = ['id','user_id','verification_token','expired_at','created_at','updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
