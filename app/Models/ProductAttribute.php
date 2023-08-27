<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_attributes';

    protected $fillable = ['product_id','name'];

    protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $dates = ['deleted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributeValues() {
        return $this->hasMany(ProductAttributeValue::class, 'product_attribute_id');
    }
}
