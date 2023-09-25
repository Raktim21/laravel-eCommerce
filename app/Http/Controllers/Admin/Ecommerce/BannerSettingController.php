<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\BannerService;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\BannerSettingRequest;

class BannerSettingController extends Controller
{
    protected $service;

    public function __construct(BannerService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = Cache::remember('banners', 60*24*60, function () {
            return $this->service->getAll();
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);

    }


    public function store(BannerSettingRequest $request)
    {
        if($this->service->store($request))
        {
            return response()->json([
                'status' => true,
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.'],
            ], 500);
        }
    }


    public function detail($id)
    {
        $data = Cache::remember('bannerSettingDetail'.$id, 24*60*60*7, function () use ($id) {
            return $this->service->read($id);
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function update(BannerSettingRequest $request, $id)
    {
        if($this->service->update($request, $id))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.'],
            ], 500);
        }
    }


    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'status' => true,
        ]);
    }


    public function bulkDelete(Request $request)
    {
        $this->service->multipleDeletes($request);

        return response()->json([
            'status' => true,
        ]);
    }

}
