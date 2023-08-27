<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTrace extends Model
{
    use HasFactory;

    protected $table = 'inventory_traces';

    protected $fillable = ['from_branch_id','to_branch_id','event_date'];

    protected $hidden = ['created_at','updated_at'];

    public function from_branch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function to_branch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function traceProducts()
    {
        return $this->hasMany(InventoryTraceProduct::class, 'trace_id');
    }
}
