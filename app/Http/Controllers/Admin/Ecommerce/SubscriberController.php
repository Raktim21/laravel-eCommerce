<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\SubscriberRequest;
use App\Http\Services\SubscriberService;

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
