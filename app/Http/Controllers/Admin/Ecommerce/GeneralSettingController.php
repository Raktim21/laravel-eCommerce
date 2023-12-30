<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Http\Services\AssetService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\GeneralSettingRequest;
use App\Http\Services\GeneralSettingService;

class GeneralSettingController extends Controller
{
    protected $service;

    public function __construct(GeneralSettingService $service)
    {
        $this->service = $service;
    }


    public function detail()
    {
        $data = Cache::remember('generalSetting', 24*60*60*7, function () {
            return $this->service->getSetting();
        });

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], is_null($data) ? 204 : 200);
    }


    public function update(GeneralSettingRequest $request)
    {
        $this->service->updateSetting($request);

        return response()->json([
            'status'  => true,
        ]);
    }

    public function faqList()
    {
        $data = Cache::remember('faqs', 24*60*60*7, function () {
            return (new AssetService())->getFaqs();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function faqStore(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'question' => 'required|string|max:500',
            'answer'   => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        (new AssetService())->storeFAQ($request);

        return response()->json([
            'status'    => true,
        ], 201);
    }

    public function faqUpdate(Request $request, $id)
    {
        $validator = Validator::make(request()->all(), [
            'question' => 'required|string|max:500',
            'answer'   => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        (new AssetService())->updateFAQ($request, $id);

        return response()->json([
            'status'    => true,
        ]);
    }

    public function faqDelete($id)
    {
        (new AssetService())->deleteFAQ($id);

        return response()->json([
            'status'    => true,
        ]);
    }
}
