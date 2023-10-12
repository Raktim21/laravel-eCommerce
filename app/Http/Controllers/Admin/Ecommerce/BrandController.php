<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Requests\BrandRequest;
use App\Http\Services\BrandService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\BrandBulkDeleteRequest;

class BrandController extends Controller
{

    protected $service;

    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        if(\request()->input('is_paginated'))
        {
            $data = Cache::remember('brands', 60 * 24 * 24, function () {
                return $this->service->getAll(false);
            });

            return response()->json([
                'status' => true,
                'data'   => $data
            ], count($data) == 0 ? 204 : 200);
        }
        else {
            $data = Cache::remember('brandList'.request()->get('page', 1), 60 * 24 * 24, function () {
                return $this->service->getAll(true);
            });

            return response()->json([
                'status' => true,
                'data'   => $data
            ], $data->isEmpty() ? 204 : 200);
        }
    }



    public function store(BrandRequest $request)
    {
        if (!$request->hasFile('image'))
        {
            return response()->json([
                'status' => false,
                'errors' => ['The image field is required.']
            ], 422);
        }
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
