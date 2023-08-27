<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRestockRequest extends Model
{
    use HasFactory;

    protected $table = 'product_restock_requests';

    protected $hidden = ['updated_at'];

    protected $fillable = ['user_id','product_id','is_stocked'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
