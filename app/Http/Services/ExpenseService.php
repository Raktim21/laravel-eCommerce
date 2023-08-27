<?php

namespace App\Http\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseService
{
    protected $expense;

    public function __construct(Expense $expense)
    {
        $this->expense  = $expense;
    }

    public function getCategories()
    {
        return ExpenseCategory::latest()->get();
    }

    public function getExpenses()
    {
        return $this->expense->clone()->with('category')
            ->latest()->paginate(20);
    }

    public function storeCategory(Request $request): void
    {
        ExpenseCategory::create([
            'name' => $request->name,
        ]);
    }

    public function updateCategory(Request $request, $id): void
    {
        ExpenseCategory::findOrFail($id)->update(['name' => $request->name]);
    }

    public function deleteCategory($id): bool
    {
        $category = ExpenseCategory::findOrfail($id);

        if ($category->expences->count() == 0) {
            $category->delete();
            return true;
        } else {
            return false;
        }
    }

    public function storeExpense(Request $request): void
    {
        $this->expense->clone()->create([
            'title'                 => $request->title,
            'expense_category_id'   => $request->expense_category_id,
            'amount'                => $request->amount,
            'event_date'            => $request->event_date,
            'description'           => $request->description
        ]);
    }

    public function updateExpense(Request $request, $id): void
    {
        $this->expense->clone()->findOrFail($id)->update([
            'title' => $request->title,
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'event_date' => Carbon::parse($request->event_date),
            'description' => $request->description
        ]);
    }

    public function deleteExpense($id): void
    {
        $this->expense->clone()->findOrFail($id)->delete();
    }

    public function multipleCategoryDelete(Request $request): void
    {
        ExpenseCategory::whereIn('id',$request->ids)->delete();
    }

    public function multipleExpenseDelete(Request $request): void
    {
        $this->expense->clone()->whereIn('id',$request->ids)->delete();
    }
}
