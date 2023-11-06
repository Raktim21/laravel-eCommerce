<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public function branches()
    {
        return $this->hasMany(BankBranch::class, 'bank_id');
    }
}
