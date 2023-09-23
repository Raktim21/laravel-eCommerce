<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriberRequest;
use App\Http\Services\SubscriberService;
use Illuminate\Support\Facades\Cache;

class SubscriberController extends Controller
{
    protected $service;

    public function __construct(SubscriberService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = Cache::remember('subscriberList'.request()->get('page', 1), 24*60*60*7, function () {
            return $this->service->getAll();
        });

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
