<?php

namespace App\Imports;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ExpenseImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return Model|null
    */
    public function model(array $row)
    {
        $category = ExpenseCategory::firstOrCreate([
            'name' => $row['category']
        ],[
            'name' => $row['category']
        ]);

        $date = $row['date'];

        $dateTime = Carbon::parse($date)->toDateTimeString();

        return new Expense([
            'title'                 => $row['title'],
            'expense_category_id'   => $category->id,
            'amount'                => $row['amount'],
            'date'                  => $dateTime,
            'description'           => $row['description']
        ]);
    }

    public function rules(): array
    {
        return [
            '*.title'       => 'required|string|max:98',
            '*.category'    => 'required|string|max:50',
            '*.amount'      => 'required|numeric',
             '*.date'       => 'required|date_format:Y-m-d',
            '*.description' => 'required|string'
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.title.required'       => 'The title field is required.',
            '*.category.required'    => 'The category field is required.',
            '*.amount.required'      => 'The amount field is required.',
            '*.date.required'        => 'The date field is required.',
            '*.description.required' => 'The description field is required.',
            '*.amount.integer'       => 'The amount field must have a valid integer.',
            '*.date.date'            => 'The date field must have a valid date.',
            '*.date.date_format'     => 'The date format must match: Y-m-d.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'errors'  => $validator->errors()->all(),
        ], 422));
    }
}
