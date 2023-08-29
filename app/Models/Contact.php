<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
