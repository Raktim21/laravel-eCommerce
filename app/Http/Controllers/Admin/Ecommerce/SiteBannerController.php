<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteBannerUpdateRequest;
use App\Http\Services\SiteBannerService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SiteBannerController extends Controller
{
    protected $service;

    public function __construct(SiteBannerService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = Cache::remember('siteBanners', 60*60*24*7, function () {
            return $this->service->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], is_null($data) ? 204 : 200);
    }


    public function update(SiteBannerUpdateRequest $request)
    {
        if($this->service->extraValidationChecker($request))
        {
            return response()->json([
                'status' => false,
                'errors' => ['Please select at least one image.']
            ], 422);
        }

        $this->service->store($request);

        return response()->json([
            'status' => true,
        ]);
    }
}
