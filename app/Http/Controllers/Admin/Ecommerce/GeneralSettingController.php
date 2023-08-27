<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatusUpdateRequest;
use App\Http\Requests\GeneralSettingRequest;
use App\Http\Services\GeneralSettingService;
use App\Http\Services\AssetService;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Mews\Purifier\Facades\Purifier;

class GeneralSettingController extends Controller
{
    protected $service;

    public function __construct(GeneralSettingService $service)
    {
        $this->service = $service;
    }


    public function detail()
    {
        $data = Cache::remember('general', 24*60*60, function () {
            return $this->service->getSetting();
        });

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], is_null($data) ? 204 : 200);
    }


    public function update(GeneralSettingRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->updateSetting($request);
        Cache::forget('general');

        return response()->json([
            'status'  => true,
        ]);
    }


    public function deliveryStatus()
    {
        $data = $this->service->getDeliveryStatus();

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], is_null($data) ? 204 : 200);
    }



    public function deliveryStatusUpdate(StatusUpdateRequest $request)
    {
        $this->service->updateDeliveryStatus($request);

        return response()->json([
            'status'  => true,
        ]);
    }

    public function faqList(): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('faqs', 24*60*60, function () {
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
        Cache::forget('faqs');

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
        Cache::forget('faqs');

        return response()->json([
            'status'    => true,
        ]);
    }

    public function faqDelete($id)
    {
        (new AssetService())->deleteFAQ($id);
        Cache::forget('faqs');

        return response()->json([
            'status'    => true,
        ]);
    }

    public function orderFaq(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'ids'       => 'required|array|size:'.FAQ::count(),
            'ids.*'     => 'required|exists:static_faqs,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        (new AssetService())->orderFAQ($request);
        Cache::forget('faqs');

        return response()->json([
            'status'    => true,
        ]);
    }
}