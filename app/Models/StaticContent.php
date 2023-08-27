<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticContent extends Model
{
    use HasFactory;

    protected $table = 'static_contents';
    protected $guarded = ['id'];
    protected $hidden = ['created_at','updated_at'];


    public function staticMenu(){
        return $this->hasMany(StaticMenu::class, 'static_contents_id');
    }


    public function scopeSearch($query)
    {
        $title       = request()->title;

        if ($title && $title != 'null') {
            $query->where('title', 'LIKE', "%{$title}%");
        }

        return $query;
    }


}
