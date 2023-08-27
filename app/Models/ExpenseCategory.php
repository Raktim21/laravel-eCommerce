<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $table = 'expense_categories';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public function expences()
    {
        return $this->hasMany(Expense::class, 'expense_category_id');
    }

    public function scopeSearch($query)
    {
        $name       = request()->name;

        if ($name && $name != 'null') {
            $query->where('name', 'LIKE', "%{$name}%");
        }

        return $query;

    }

}
