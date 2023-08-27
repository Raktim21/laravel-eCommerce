<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCombinationValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_combination_values';

    protected $guarded = ['combination_id','att_value_id'];

    protected $fillable = ['combination_id','att_value_id'];

    protected $hidden = ['created_at','updated_at'];

    protected $dates = ['deleted_at'];


    public function combination()
    {
        return $this->belongsTo(ProductCombination::class, 'combination_id');
    }

    public function attributeValue()
    {
        return $this->belongsTo(ProductAttributeValue::class, 'att_value_id');
    }
}
