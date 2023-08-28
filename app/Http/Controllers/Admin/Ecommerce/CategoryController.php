<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryBulkDeleteRequest;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Requests\ReOrderRequest;
use App\Http\Services\CategoryService;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    protected $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }



    public function index()
    {
        $data = $this->service->getAll(request()->input('is_paginated') ?? 1, true);

        return response()->json([
            'status' => true,
            'data'   => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function store(CategoryStoreRequest $request)
    {
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


    public function reorder(ReOrderRequest $request)
    {
        $this->service->shuffleCategories($request);

        return response()->json([
            'status' => true,
        ]);
    }


    public function statusUpdate($id): \Illuminate\Http\JsonResponse
    {
        $this->service->changeStatus($id);

        return response()->json([
            'status'  => true,
        ]);
    }


    public function bulkDelete(CategoryBulkDeleteRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->deleteCategories($request);

        return response()->json([
            'status' => true,
        ]);
    }
}

