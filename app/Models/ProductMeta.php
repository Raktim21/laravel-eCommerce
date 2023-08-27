<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMeta extends Model
{
    use HasFactory;

    protected $table = 'product_metas';

    protected $fillable = ['product_id','meta_title','meta_description','meta_keywords','meta_image'];

    protected $hidden = ['created_at','updated_at'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
