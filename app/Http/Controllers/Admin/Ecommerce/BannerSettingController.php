<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerSettingRequest;
use App\Http\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use OpenApi\Annotations as OA;

class BannerSettingController extends Controller
{
    protected $service;

    public function __construct(BannerService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->getAll()
        ]);

    }


    public function store(BannerSettingRequest $request)
    {
        if($this->service->store($request))
        {
            Artisan::call('cache:clear');
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
        return response()->json([
            'status' => true,
            'data' => $this->service->read($id)
        ]);
    }


    public function update(BannerSettingRequest $request, $id)
    {
        if($this->service->update($request, $id))
        {
            Artisan::call('cache:clear');
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
