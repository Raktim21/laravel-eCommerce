<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoCodeBulkDeleteRequest;
use App\Http\Requests\PromoCreateRequest;
use App\Http\Requests\PromoUpdateRequest;
use App\Http\Services\PromoCodeService;
use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class PromocodeController extends Controller
{

    protected $service;

    public function __construct(PromoCodeService $service)
    {
        $this->service = $service;
    }


    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('promoCodeList'.request()->get('page', 1), 24*60*60, function () {
            return $this->service->getList();
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function store(PromoCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        if($this->service->store($request))
        {
            return response()->json([
                'status' => true,
            ],201);
        }
        else {
            return response()->json([
                'status' => false,
                'errors'   => ['something went wrong.']
            ],500);
        }
    }


    public function detail($id): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('promoCodeDetail'.$id, 24*60*60, function () use ($id) {
            return $this->service->get($id);
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function update(PromoUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        $this->service->update($request, $id);
        return response()->json([
            'status' => true,
        ]);
    }

    public function updateStatus($id): \Illuminate\Http\JsonResponse
    {
        if ($this->service->updateStatus($id))
        {
            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => true,
                'errors' => ['This promo code has already been expired.']
            ], 400);
        }
    }
}
