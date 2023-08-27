<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopReviews extends Model
{
    use HasFactory;

    protected $table = 'shop_reviews';

    protected $fillable = ['user_id','guest_session_id','review','rating','is_published'];

    protected $hidden = ['updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
