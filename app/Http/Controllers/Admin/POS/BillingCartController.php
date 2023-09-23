<?php

namespace App\Http\Controllers\Admin\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\BillingStoreRequest;
use App\Http\Services\BillingService;
use Illuminate\Support\Facades\Cache;

class BillingCartController extends Controller
{

    protected $service;

    public function __construct(BillingService $service)
    {
        $this->service = $service;
    }


    public function cartList()
    {
        $data = Cache::remember('billingList'.request()->get('page', 1), 24*60*60, function () {
            return $this->service->getCart();
        });

        return response()->json([
                'status' => true,
                'data' => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function cartDetail($id)
    {
        Cache::clear();
        $data = Cache::remember('billiDetail'.$id, 24*60*60, function () use ($id) {
            return $this->service->getData($id);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], is_null($data) ? 204 : 200);
    }


    public function cartStore(BillingStoreRequest $request)
    {
        $cart_id = $this->service->store($request);

        if($cart_id != 0)
        {
            return response()->json([
                'status' => true,
                'data'   => array('billing_id' => $cart_id)
            ], 201);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.'],
            ], 500);
        }
    }


    public function convertBilling($id)
    {
        $status = $this->service->convert($id);

        if($status == 1)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Order is already placed.']
            ], 400);
        }
        else if($status == 2)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Select customer first.']
            ], 422);
        }
        else if($status == 3)
        {
            return response()->json([
                'status' => true,
            ], 201);
        }
        else if($status == 4)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Some of the selected product is out of stock.']
            ], 400);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong']
            ], 500);
        }
    }
}
