<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    public static function boot()
    {
        parent::boot();

        static::created(function ($request) {
            forgetCaches('productRestockRequests');
        });

        static::deleted(function ($request) {
            forgetCaches('productRestockRequests');
        });
    }
}
