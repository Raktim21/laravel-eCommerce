<?php

namespace App\Http\Controllers\Admin\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenceRequest;
use App\Http\Requests\ExpenseCategoryBulkDeleteRequest;
use App\Http\Services\ExpenseService;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    protected $service;

    public function __construct(ExpenseService $service)
    {
        $this->service      = $service;
    }


    public function categoryIndex()
    {
        $data = $this->service->getCategories();
        return response()->json([
            'status'        => true,
            'data'          => $data,
        ], count($data) == 0 ? 204 : 200);
    }


    public function categoryStore(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'  => 'required|string|max:50|unique:expense_categories,name',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors'  => $validator->errors()->all(),
            ],422);
        }

        $this->service->storeCategory($request);

        return response()->json([
            'status'  => true,
        ],201);
    }


    public function categoryUpdate(Request $request,$id)
    {
        $validator = Validator::make($request->all(),[
            'name'  => 'required|string|max:255|unique:expense_categories,name,'.$id,
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all(),
            ],422);
        }

        $this->service->updateCategory($request, $id);

        return response()->json([
            'status'  => true,
        ]);
    }


    public function categoryDelete($id)
    {
        if ($this->service->deleteCategory($id))
        {
            return response()->json([
                'status'  => true,
            ]);

        } else {
            return response()->json([
                'status'  => false,
                'errors'  => ['The selected expense category can not be deleted.'],
            ],422);
        }
    }


    public function expenseIndex()
    {
        $data = $this->service->getExpenses();

        return response()->json([
            'status'           => true,
            'data'             => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function expenseStore(ExpenceRequest $request)
    {
        $this->service->storeExpense($request);

        return response()->json([
            'status'  => true,
        ],201);
    }


    public function expenseUpdate(ExpenceRequest $request, $id)
    {
        $this->service->updateExpense($request, $id);

        return response()->json([
            'status'  => true,
        ]);
    }

    public function expenseDelete($id)
    {
        $this->service->deleteExpense($id);

        return response()->json([
            'status'  => true,
        ]);
    }

    public function categoryBulkDelete(ExpenseCategoryBulkDeleteRequest $request)
    {
        $this->service->multipleCategoryDelete($request);

        return response()->json([
            'status' => true,
        ]);
    }


    public function expenseBulkDelete(Request $request)
    {
        $this->service->multipleExpenseDelete($request);

        return response()->json([
            'status' => true,
        ]);
    }

}
