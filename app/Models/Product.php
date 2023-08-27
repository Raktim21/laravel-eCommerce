<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $guarded = ['id','uuid'];

    protected $fillable = ['uuid','slug','name','short_description','description',
        'thumbnail_image','featured_image','brand_id','category_id','category_sub_id',
        'display_price','previous_display_price','view_count','sold_count','review_count',
        'is_on_sale','is_featured','status'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $dates = ['deleted_at'];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(ProductSubCategory::class, 'category_sub_id');
    }

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function meta()
    {
        return $this->hasOne(ProductMeta::class, 'product_id');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImages::class, 'product_id');
    }

    public function productCombinations()
    {
        return $this->HasMany(ProductCombination::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasManyThrough(OrderItems::class, ProductCombination::class);
    }

    public function productReviewRating()
    {
        return $this->orderItems()
            ->join('product_reviews', 'order_items.id', '=', 'product_reviews.order_item_id')
            ->selectRaw('AVG(product_reviews.rating) as avg_review_rating')
            ->groupBy('product_id');
    }

    public function productReviews()
    {
        return $this->orderItems()
            ->join('product_reviews', 'order_items.id', '=', 'product_reviews.order_item_id')
            ->select('product_reviews.*');
    }

    public function productAbuseReports()
    {
        return $this->hasMany(ProductAbuseReport::class, 'product_id');
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id');
    }

    public function requests()
    {
        return $this->hasMany(ProductRestockRequest::class, 'product_id');
    }

    public function inventories()
    {
        return $this->hasManyThrough(Inventory::class, ProductCombination::class);
    }

    public function defaultCombination()
    {
        return $this->productCombinations()->where('is_default', 1);
    }

    public function promos()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_products', 'product_id');
    }

    public function scopeSearch($query)
    {
        $search       = request()->search;
        $product_uuid = request()->product_uuid;
        $category     = request()->category;
        $subCategory  = request()->subCategory;
        $brand        = request()->brand;
        $minPrice     = request()->minPrice;
        $maxPrice     = request()->maxPrice;
        $flash_sell   = request()->flash_sell;
        $featured     = request()->featured;

        if ($search && $search != 'null') {
            $query->where('products.name', 'LIKE', "%{$search}%");
        }
        if ($product_uuid && $product_uuid != 'null') {
            $query->where('products.product_uuid',  $product_uuid);
        }

        if ($category && $category != 'null') {
            $query->where('products.category_id', $category);
        }

        if ($subCategory && $subCategory != 'null') {
            $query->where('products.sub_category_id', $subCategory);
        }

        if ($brand && $brand != 'null') {
            $query->where('products.brand_id', $brand);
        }

        if ($minPrice && $maxPrice) {
            $query->whereBetween('products.price', [$minPrice, $maxPrice]);
        }

        if ($flash_sell && $flash_sell == 1 && FlashSale::first() != null && FlashSale::first()->status == 1) {
            $query->where('products.is_on_sell', $flash_sell);
        }

        if ($featured && $featured == 1) {
            $query->where('products.is_featured', $featured);
        }

        return $query;

    }


}
