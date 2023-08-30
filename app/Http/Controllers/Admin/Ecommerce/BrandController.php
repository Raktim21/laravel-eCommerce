<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandBulkDeleteRequest;
use App\Http\Requests\BrandRequest;
use App\Http\Services\BrandService;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BrandController extends Controller
{

    protected $service;

    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = Cache::remember('allBrands', 60*24*24, function () {
            return $this->service->getAll(!request()->input('is_paginated'));
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], $data->isEmpty() ? 204 : 200);
    }



    public function store(BrandRequest $request)
    {
        $this->service->store($request);

        return response()->json([
            'status'  => true,
        ], 201);
    }



    public function update(BrandRequest $request, $id)
    {
        $this->service->update($request, $id);

        return response()->json([
            'status' => true
        ]);
    }



    public function destroy($id)
    {
        if($this->service->delete($id))
        {
            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => ['Selected brand cannot be deleted.']
            ], 400);
        }
    }


    public function bulkDelete(BrandBulkDeleteRequest $request)
    {
        $this->service->multipleDelete($request);

        return response()->json([
            'status' => true,
        ]);
    }
}
