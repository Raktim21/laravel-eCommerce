<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public function subCategories()
    {
        return $this->hasMany(ProductSubCategory::class, 'category_id')->latest();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function($category) {
            Cache::delete('categories');
            forgetCaches('categoryList');
        });

        static::updated(function($category) {
            Cache::delete('categories');
            forgetCaches('categoryList');
        });

        static::deleted(function($category) {
            Cache::delete('categories');
            forgetCaches('categoryList');
        });
    }
}
