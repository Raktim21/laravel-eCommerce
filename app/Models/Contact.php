<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contact_forms';

    protected $guarded = ['user_id'];

    protected $fillable = ['user_id','guest_session_id','first_name','last_name',
        'email','phone','message','ip_address'];

    protected $hidden = ['updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
