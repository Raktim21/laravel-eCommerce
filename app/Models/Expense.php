<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $guarded = ['id'];

    protected $hidden = ['created_at','updated_at'];


    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($expense) {
            forgetCaches('expenseList');
        });

        static::updated(function ($expense) {
            forgetCaches('expenseList');
        });

        static::deleted(function ($expense) {
            forgetCaches('expenseList');
        });
    }
}
