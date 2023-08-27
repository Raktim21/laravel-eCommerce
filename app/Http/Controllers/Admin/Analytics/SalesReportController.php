<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\DateRequest;
use App\Http\Requests\YearRequest;
use App\Http\Services\ReportService;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    protected $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }


    public function generalReport()
    {
        $data = $this->service->getGeneralData();

        return response()->json([
            'status' => true,
            'data'   => $data
        ], is_null($data) ? 204 : 200);
    }


    public function mostViewedProducts()
    {
        $data = $this->service->getProducts();

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function newUsers(YearRequest $request)
    {
        $data = $this->service->getUsers($request->year ?? date('Y'));

        return response()->json([
            'status'  => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function mostPurchasedUsers(DateRequest $request)
    {
        $data = $this->service->getMostActiveUsers($request);

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function mostSoldProducts(DateRequest $request)
    {
        $data = $this->service->soldProducts($request);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }


    public function productReport(DateRequest $request, $product_id)
    {
        $data = $this->service->productData($request, $product_id);

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function salesData(YearRequest $request)
    {
        $data = $this->service->sales($request->year ?? date('Y'));

        return response()->json([
            'status'  => true,
            'data' => $data
        ]);
    }


    public function mostOrderedCategories(DateRequest $request)
    {
        $data = $this->service->orderedCategories($request);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
