<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Services\FlashSaleService;
use App\Http\Requests\FlashSaleCreateRequest;

class FlashSaleController extends Controller
{
    protected $service;

    public function __construct(FlashSaleService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = Cache::remember('flashSale', 60*60*24, function () {
            return $this->service->getSale();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ],is_null($data) ? 204 : 200);
    }


    public function store(FlashSaleCreateRequest $request)
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
        if($this->service->updateSaleStatus())
        {
            Cache::clear();
            return response()->json(['status' => true]);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Flash sale cannot be activated due to exceeding time limit.']
        ], 422);
    }
}
