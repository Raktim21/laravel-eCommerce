<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventories';

    protected $guarded = ['shop_branch_id','product_combination_id'];

    protected $fillable = ['shop_branch_id','product_combination_id',
        'stock_quantity','damage_quantity','deleted_at'];

    protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $dates = ['deleted_at'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'shop_branch_id');
    }

    public function combination()
    {
        return $this->belongsTo(ProductCombination::class, 'product_combination_id');
    }
}
