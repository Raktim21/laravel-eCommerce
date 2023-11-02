<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductReview extends Model
{
    use HasFactory;

    protected $table = 'product_reviews';

    protected $guarded = ['order_item_id'];

    protected $fillable = ['order_item_id','review','reply_from_merchant','rating','is_published'];

    protected $hidden = ['updated_at'];

    protected $casts = [
        'is_published'    => 'boolean',
    ];

    public function images()
    {
        return $this->hasMany(ProductReviewImage::class, 'product_review_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItems::class, 'order_item_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            forgetCaches('allProductReviews');
            forgetCaches('productReviews'.$review->orderItem->combination->product_id);
            Cache::delete('customer_order_detail'.$review->orderItem->order_id);
        });

        static::updated(function ($review) {
            forgetCaches('product_reviews'.$review->orderItem->combination->product_id);
            forgetCaches('productReviews'.$review->orderItem->combination->product_id);
            forgetCaches('allProductReviews');
            forgetCaches('product_reviews');
            Cache::delete('productReview'.$review->id);
            Cache::delete('product_reviews');
        });
    }
}
