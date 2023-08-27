<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticMenu extends Model
{
    use HasFactory;

    protected $table = 'static_menus';
    protected $guarded = ['id'];
    protected $hidden = ['created_at','updated_at'];


    public function staticContent(){
        return $this->belongsTo(StaticContent::class, 'static_contents_id');
    }

    public function staticMenuType(){
        return $this->belongsTo(StaticMenuType::class, 'static_menu_type_id');
    }


    public function scopeSearch($query)
    {
        $title       = request()->title;

        if ($title && $title != 'null') {
            $query->where('menu_name', 'LIKE', "%{$title}%");
        }

        return $query;
    }
}
