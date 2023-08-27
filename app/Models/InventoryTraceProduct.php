<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTraceProduct extends Model
{
    use HasFactory;

    protected $table = 'inventory_trace_products';

    protected $fillable = ['trace_id','product_combination_id','product_quantity'];

    protected $hidden = ['created_at','updated_at'];

    public function productCombination()
    {
        return $this->belongsTo(ProductCombination::class, 'product_combination_id');
    }

    public function trace()
    {
        return $this->belongsTo(InventoryTrace::class, 'trace_id');
    }
}
