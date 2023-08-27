<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReviewImage extends Model
{
    use HasFactory;

    protected $table = 'product_review_images';

    protected $fillable = ['product_review_id','image'];

    protected $hidden = ['created_at','updated_at'];

    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }
}
