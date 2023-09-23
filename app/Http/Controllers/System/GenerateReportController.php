<?php

namespace App\Http\Controllers\System;

use App\Models\Order;
use App\Models\BillingCart;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Imports\ExpenseImport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ExpensesExport;
use App\Exports\InventoryExport;
use App\Http\Services\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Services\ReportService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Services\BillingService;
use App\Http\Requests\FileTypeRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\GeneralSettingService;

class GenerateReportController extends Controller
{

    protected $service1, $service2;

    public function __construct(GeneralSettingService $service1, ReportService $service2)
    {
        $this->service1 = $service1;
        $this->service2 = $service2;
    }


    public function invoicePDF($order_id)
    {
        $order = (new OrderService(new Order()))->getData($order_id);

        $general = $this->service1->getSetting();

        $data = array(
            'order' => $order,
            'general' => $general,
            'title' => $order->order_number
        );

        $pdf = PDF::loadView('invoice', $data);

        return $pdf->stream('invoice_'. $order->order_number .'.pdf');
    }


    public function exportExpense(FileTypeRequest $request)
    {
        $file_name = 'expense_data' . date('dis') . '.' . $request->type;
        return Excel::download(new ExpensesExport, $file_name);
    }


    public function importExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,csv',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $file = $request->file('file');

        try {
            Excel::import(new ExpenseImport, $file);

            return response()->json([
                'status' => true,
            ], 201);
        }
        catch (\Exception $ex)
        {
            return response()->json(['error' => $ex->getMessage()], 422);
        }
    }


    public function exportOrder(FileTypeRequest $request)
    {
        $file_name = 'order_data' . date('dis') . '.' . $request->type;

        return Excel::download(new OrdersExport, $file_name);
    }


    public function exportInventory(FileTypeRequest $request)
    {
        $file_name = 'inventory_data' . date('dis') . '.' . $request->type;

        return Excel::download(new InventoryExport, $file_name);
    }


    public function billingPDF($billing_id)
    {
        $cart = (new BillingService(new BillingCart()))->getData($billing_id);

        $general = $this->service1->getSetting();

        $data = array(
            'cart' => $cart,
            'general' => $general,
            'title' => $cart->billing_number
        );

        $pdf = PDF::loadView('billing_invoice', $data);

        return $pdf->stream('billing_'. $cart->billing_number .'.pdf');
    }
}
