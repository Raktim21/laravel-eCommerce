<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSubCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories_sub';

    protected $fillable = ['category_id','name','slug','image'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function($sub_category) {
            Product::withTrashed()->where('category_sub_id',$sub_category->id)->update([
                'category_sub_id' => null,
            ]);
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
