<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReOrderRequest;
use Illuminate\Support\Facades\Cache;
use App\Http\Services\CategoryService;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Requests\CategoryBulkDeleteRequest;

class CategoryController extends Controller
{
    protected $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = $this->service->getAll(request()->has('is_paginated') ? 0 : 1, true);

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }


    public function store(CategoryStoreRequest $request)
    {
        if (!$request->hasAny(['image', 'image_id']))
        {
            return response()->json([
                'status' => false,
                'errors' => ['Please select an image.']
            ], 422);
        }
        $this->service->store($request);

        return response()->json([
            'status' => true,
        ],201);
    }


    public function update(CategoryUpdateRequest $request, $id)
    {
        $this->service->update($request, $id);

        return response()->json([
            'status' => true
        ]);
    }


    public function reorder(ReOrderRequest $request)
    {
        $this->service->shuffleCategories($request);

        return response()->json([
            'status' => true,
        ]);
    }


    public function statusUpdate($id)
    {
        $this->service->changeStatus($id);

        return response()->json([
            'status'  => true,
        ]);
    }


    public function destroy($id)
    {
        if($this->service->delete($id)) {
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Selected category cannot be deleted.']
        ], 400);
    }


    public function bulkDelete(CategoryBulkDeleteRequest $request)
    {
        $this->service->deleteCategories($request);

        return response()->json([
            'status' => true,
        ]);
    }
}

