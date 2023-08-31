<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductImages extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = ['product_id','image'];

    protected $hidden = ['created_at','updated_at'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($image) {
            Cache::delete('productDetail'.$image->product_id);
        });
    }
}
