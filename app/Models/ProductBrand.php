<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBrand extends Model
{
    use HasFactory;

    protected $table = 'product_brands';

    protected $fillable = ['name','slug','image'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function($brand) {
            Product::withTrashed()->where('brand_id',$brand->id)->update([
                'brand_id' => null,
            ]);
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
