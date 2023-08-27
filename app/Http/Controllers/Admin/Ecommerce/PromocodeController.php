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
use Illuminate\Support\Facades\Validator;

class PromocodeController extends Controller
{

    protected $service;

    public function __construct(PromoCodeService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = $this->service->getList();

        return response()->json([
            'status' => true,
            'data' => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function store(PromoCreateRequest $request)
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

    public function update(PromoUpdateRequest $request, $id)
    {
        $this->service->update($request, $id);
        return response()->json([
            'status' => true,
        ]);
    }

    public function updateStatus($id)
    {
        $this->service->updateStatus($id);
        return response()->json([
            'status' => true,
        ]);
    }


    public function detail($id)
    {
        $data = $this->service->get($id);

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }
}
