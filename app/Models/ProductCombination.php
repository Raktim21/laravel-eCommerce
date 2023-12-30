<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCombination extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_combinations';

    protected $guarded = ['product_id'];

    protected $fillable = ['product_id','cost_price','selling_price','weight',
        'is_default','is_active'];

    protected $hidden = ['created_at','updated_at'];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_default'   => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributeValues()
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_combination_values', 'combination_id', 'att_value_id');
    }

    public function wishlistItem()
    {
        return $this->hasMany(WishListItem::class, 'product_combination_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'product_combination_id');
    }



    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'product_combination_id');
    }


    public function inventoryTrace()
    {
        return $this->hasMany(InventoryTraceProduct::class, 'product_combination_id');
    }

    public function cart()
    {
        return $this->hasMany(CustomerCart::class, 'product_combination_id');
    }

    public function billing_cart()
    {
        return $this->hasMany(BillingCartItems::class, 'product_combination_id');
    }
}
