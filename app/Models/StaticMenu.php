<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StaticMenu extends Model
{
    use HasFactory;

    protected $table = 'static_menus';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public function parentMenu() {
        return $this->belongsTo(StaticMenu::class, 'parent_menu_id');
    }

    public function childMenus() {
        return $this->hasMany(StaticMenu::class, 'parent_menu_id');
    }

    public function staticContent() {
        return $this->belongsTo(StaticContent::class, 'static_contents_id');
    }

    public function staticMenuType() {
        return $this->belongsTo(StaticMenuType::class, 'static_menu_type_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($menu) {
            Cache::delete('staticMenus');
            forgetCaches('staticMenuList');
            Cache::delete('static_menus');
        });

        static::updated(function ($menu) {
            Cache::delete('staticMenus');
            forgetCaches('staticMenuList');
            Cache::delete('staticMenuDetail'.$menu->id);
            Cache::delete('static_menus');
            Cache::delete('static_menu_detail'.$menu->id);

            foreach ($menu->childMenus() as $item)
            {
                Cache::delete('staticMenuDetail'.$item->id);
                Cache::delete('static_menu_detail'.$menu->id);
            }
        });

        static::deleted(function ($menu) {
            Cache::delete('staticMenus');
            forgetCaches('staticMenuList');
            Cache::delete('static_menus');
            Cache::delete('staticMenuDetail'.$menu->id);
            Cache::delete('static_menu_detail'.$menu->id);
        });
    }
}
