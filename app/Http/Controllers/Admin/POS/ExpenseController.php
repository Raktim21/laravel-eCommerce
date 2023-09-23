<?php

namespace App\Http\Controllers\Admin\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Http\Requests\ExpenseCategoryBulkDeleteRequest;
use App\Http\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $data = Cache::remember('expenseCategories', 60*60*24*7, function () {
            return $this->service->getCategories();
        });

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
        $data = Cache::remember('expenseList'.request()->get('page', 1), 24*60*60, function () {
            return $this->service->getExpenses();
        });

        return response()->json([
            'status'           => true,
            'data'             => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function expenseStore(ExpenseRequest $request)
    {
        $this->service->storeExpense($request);

        return response()->json([
            'status'  => true,
        ],201);
    }


    public function expenseUpdate(ExpenseRequest $request, $id)
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
