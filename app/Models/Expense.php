<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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



    public function scopeSearch($query)
    {
        $title       = request()->title;
        $category_id = request()->expence_category_id;
        $minAmount   = request()->min_amount;
        $maxAmount   = request()->max_amount;
        $startDate   = request()->start_date;
        $endDate     = request()->end_date;

        if ($title && $title != 'null') {
            $query->where('title', 'LIKE', "%{$title}%");
        }

        if ($category_id && $category_id != 'null') {
            $query->where('expence_category_id', $category_id);
        }



        if ($minAmount != 'null' && $maxAmount != 'null') {
            if($minAmount || $maxAmount){
                if ($minAmount && !$maxAmount) {
                    $query->where('amount', '>=', $minAmount);
                }elseif (!$minAmount && $maxAmount) {
                    $query->where('amount', '<=', $maxAmount);
                }else {
                    $query->whereBetween('amount', [$minAmount, $maxAmount]);
                }
            }
        }



        if ($startDate != 'null' && $endDate != 'null') {
            if($startDate || $endDate){
                if ($startDate && !$endDate) {
                    $query->where('date', '>=', Carbon::parse($startDate));
                }elseif (!$startDate && $endDate) {
                    $query->where('date', '<=', Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59));
                }else {
                    $query->whereBetween('date', [Carbon::parse($startDate), Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)]);
                }
            }
        }




        return $query;

    }
}
