<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriberRequest;
use App\Http\Services\SubscriberService;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    protected $service;

    public function __construct(SubscriberService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = $this->service->getAll();

        return response()->json([
            'status' => true,
            'data' => $data
        ], $data->isEmpty() ? 204 : 200);
    }


    public function create(SubscriberRequest $request)
    {
        $this->service->store($request);

        return response()->json([
            'status' => true,
        ], 201);
    }
}
