<?php

namespace App\Http\Controllers\Admin\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryTransferRequest;
use App\Http\Services\InventoryService;
use App\Http\Services\ProductService;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{

    protected $service;

    public function __construct(InventoryService $service)
    {
        $this->service = $service;
    }


    public function getList()
    {
        $data = $this->service->inventoryList(1);

        return response()->json([
            'status'   => true,
            'data'     => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function getLog()
    {
        $data = $this->service->inventoryLog();

        return response()->json([
            'status'   => true,
            'data'     => $data
        ], $data->isEmpty() ? 204 : 200);
    }



    public function updateStock(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'stock_quantity' => 'required|numeric|min:0|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $status = $this->service->updateStock($request->stock_quantity, $id);

        if($status == 1) {
            return response()->json([
                'status' => false,
                'errors' => ['You are not authorized to update the stock.']
            ], 403);
        }
        return response()->json([
            'status'   => true,
        ]);
    }


    public function updateDamage(Request $request, $id)
    {
        $product = Inventory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'damage_count' => 'required|numeric|min:0|max:'.($product->stock_quantity + $product->damage_quantity),
            'type'         => 'required|string|in:update,restore,delete'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'errors'   => $validator->errors()->all(),
            ],422);
        }

        if($product->shop_branch_id != auth()->guard('admin-api')->user()->shop_branch_id &&
            !auth()->guard('admin-api')->user()->hasRole('Super Admin')) {
            return response()->json([
                'status' => false,
                'errors' => ['You are not authorized to update the stock.']
            ], 403);
        }

        if($this->service->updateDamage($request, $product)) {
            return response()->json([
                'status'   => true,
            ]);
        }

        return response()->json([
            'status'   => false,
            'errors'  => ['Insufficient quantity'],
        ],422);
    }

    public function transferStock(InventoryTransferRequest $request)
    {
        if($this->service->transfer($request))
        {
            return response()->json(['status' => true], 201);
        } else {
            return response()->json(['status' => false], 500);
        }
    }
}
