<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryBulkDeleteRequest;
use App\Http\Requests\SubCategoryRequest;
use App\Http\Services\SubCategoryService;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    protected $service;

    public function __construct(SubCategoryService $service)
    {
        $this->service = $service;
    }

    public function getList($category_id): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('subCategories'.$category_id, 24*60*60*7, function () use ($category_id) {
            return $this->service->getSubCategories($category_id);
        });

        return response()->json([
            'status'        => true,
            'data'          => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function store(SubCategoryRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->store($request);

        return response()->json([
            'status' => true
        ],201);
    }


    public function update(SubCategoryRequest $request,$id): \Illuminate\Http\JsonResponse
    {
        $this->service->update($request, $id);

        return response()->json([
            'status'  => true,
        ]);
    }


    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'status'  => true,
        ]);
    }

    public function bulkDelete(SubCategoryBulkDeleteRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->multipleDelete($request);

        return response()->json([
            'status'  => true,
        ]);
    }
}

