<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoUser extends Model
{
    use HasFactory;

    protected $table = 'promo_users';

    protected $fillable = ['user_id','promo_id','usage_number'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function promo()
    {
        return $this->belongsTo(PromoCode::class, 'promo_id');
    }
}
