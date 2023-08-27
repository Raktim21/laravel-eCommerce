<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpensesExport implements FromCollection, WithHeadings
{
    /**
    * @return Collection
    */
    public function collection()
    {
        $data = Expense::select('expenses.*', 'expense_categories.name as expense_category_name')
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->latest()
            ->get();

        $result = array();

        foreach ($data as $key=>$item)
        {
            $result[$key] = array(
                '#' => $key + 1,
                'name' => $item->title,
                'cat_name' => $item->expense_category_name,
                'amount' => $item->amount,
                'time' => $item->date
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            '#','Title','Category','Amount','Time'
        ];
    }
}
