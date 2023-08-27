<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAbuseReport extends Model
{
    use HasFactory;

    protected $table = 'product_abuse_reports';

    protected $fillable = ['user_id','guest_session_id','email','phone_no',
        'product_id','complaint_notes','is_checked'];

    protected $guarded = ['guest_session_id','user_id','product_id'];

    protected $hidden = ['updated_at'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
