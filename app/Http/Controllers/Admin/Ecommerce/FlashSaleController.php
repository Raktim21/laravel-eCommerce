<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\FlashSaleCreateRequest;
use App\Http\Services\FlashSaleService;

class FlashSaleController extends Controller
{
    protected $service;

    public function __construct(FlashSaleService $service)
    {
        $this->service = $service;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = $this->service->getSale();

        return response()->json([
            'status' => true,
            'data'   => $data
        ],$data['flash_sale'] == null ? 204 : 200);
    }


    public function store(FlashSaleCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        if($this->service->updateSale($request))
        {
            return response()->json([
                'status'    => true,
            ],201);
        }

        return response()->json([
            'status'    => false,
            'errors'    => ['Something went wrong.']
        ],500);
    }

    public function changeStatus()
    {
        $this->service->updateSaleStatus();

        return response()->json(['status' => true]);
    }
}
