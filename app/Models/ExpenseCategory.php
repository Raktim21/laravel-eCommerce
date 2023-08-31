<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $table = 'expense_categories';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_category_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($cat) {
            Cache::delete('expenseCategories');
        });

        static::updated(function ($cat) {
            Cache::delete('expenseCategories');
            forgetCaches('expenseList');
        });

        static::deleted(function ($cat) {
            Cache::delete('expenseCategories');
        });
    }
}
