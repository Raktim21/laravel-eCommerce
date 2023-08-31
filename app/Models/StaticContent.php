<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StaticContent extends Model
{
    use HasFactory;

    protected $table = 'static_contents';
    protected $guarded = ['id'];
    protected $hidden = ['created_at','updated_at'];


    public function staticMenu()
    {
        return $this->hasMany(StaticMenu::class, 'static_contents_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($content) {
            Cache::delete('staticContents');
            forgetCaches('staticContentList');
        });

        static::updated(function ($content) {
            Cache::delete('staticMenus');
            Cache::delete('staticContents');
            forgetCaches('staticMenuList');
            forgetCaches('staticContentList');
            Cache::delete('static_menus');
            Cache::delete('staticContent'.$content->id);

            foreach ($content->staticMenu() as $item)
            {
                Cache::delete('staticMenuDetail'.$item->id);
                Cache::delete('static_menu_detail'.$item->id);
            }
        });

        static::deleted(function ($content) {
            Cache::delete('staticContents');
            forgetCaches('staticContentList');
            Cache::delete('staticContent'.$content->id);
        });
    }
}
