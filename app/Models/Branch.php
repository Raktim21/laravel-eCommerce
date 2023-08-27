<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'shop_branches';

    protected $fillable = ['name','address','latitude','longitude'];

    protected $hidden = ['created_at','updated_at'];

    public function admins()
    {
        return $this->hasMany(User::class, 'shop_branch_id');
    }

    public function inventories()
    {
        return $this->belongsTo(Inventory::class, 'shop_branch_id');
    }

    public function fromInventoryTrace()
    {
        return $this->hasMany(InventoryTrace::class, 'from_branch_id');
    }

    public function toInventoryTrace()
    {
        return $this->hasMany(InventoryTrace::class, 'to_branch_id');
    }

    public function users()
    {
        return $this->hasMany(Admin::class, 'branch_id');
    }
}
