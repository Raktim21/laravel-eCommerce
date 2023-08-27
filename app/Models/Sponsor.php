<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;

    protected $table = 'site_sponsors';

    protected $fillable = ['name','image','url'];

    protected $hidden = ['created_at','updated_at'];
}
