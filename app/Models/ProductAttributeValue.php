<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttributeValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_attribute_values';

    protected $fillable = ['product_attribute_id','name'];

    protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $dates = ['deleted_at'];

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function combinations()
    {
        return $this->belongsToMany(ProductCombination::class, 'product_combination_values', 'att_value_id', 'combination_id');
    }
}
