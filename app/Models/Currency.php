<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $hidden = ['created_at', 'updated_at'];

    public function default_currency()
    {
        return $this->hasOne(GeneralSetting::class, 'currency_id');
    }
}
