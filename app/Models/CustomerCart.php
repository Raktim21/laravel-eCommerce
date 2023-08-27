<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCart extends Model
{
    use HasFactory;

    protected $table = 'customer_carts';

    protected $fillable = ['user_id','guest_session_id','product_combination_id','product_quantity'];

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public function productCombination()
    {
        return $this->belongsTo(ProductCombination::class, 'product_combination_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
