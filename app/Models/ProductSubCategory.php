<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductSubCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories_sub';

    protected $fillable = ['category_id','name','slug','image'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($sub_category) {
            Cache::delete('subCategories'.$sub_category->category_id);
            Cache::delete('all_categories');
            Cache::delete('sub_categories'.$sub_category->category_id);
        });

        static::updated(function ($sub_category) {
            Cache::delete('subCategories'.$sub_category->category_id);
            Cache::delete('all_categories');
            Cache::delete('sub_categories'.$sub_category->category_id);

            foreach ($sub_category->products as $item)
            {
                Cache::delete('product_detail_'.$item->id);
                Cache::delete('productDetail'.$item->id);
            }
        });

        static::deleting(function($sub_category) {
            Product::withTrashed()->where('category_sub_id',$sub_category->id)->update([
                'category_sub_id' => null,
            ]);
        });

        static::deleted(function ($sub_category) {
            Cache::delete('subCategories'.$sub_category->category_id);
            Cache::delete('all_categories');
            Cache::delete('sub_categories'.$sub_category->category_id);
        });
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_sub_id');
    }
}
