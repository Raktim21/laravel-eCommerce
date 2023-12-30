<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SponsorRequest;
use App\Http\Services\SponsorService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SponsorController extends Controller
{
    protected $service;

    public function __construct(SponsorService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $data = Cache::remember('sponsors', 60*60*24*30, function () {
            return $this->service->getAll();
        });

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function store(SponsorRequest $request)
    {
        if (!$request->hasAny(['image', 'image_id']))
        {
            return response()->json([
                'status' => false,
                'errors' => ['Please select an image.']
            ], 422);
        }
        $this->service->store($request);

        return response()->json([
            'status' => true,
        ], 201);
    }


    public function update(SponsorRequest $request, $id)
    {
        $this->service->update($request, $id);

        return response()->json([
            'status' => true,
        ]);
    }


    public function delete($id)
    {
        $this->service->delete($id);

        return response()->json([
            'status' => true,
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'ids'       => 'required|array',
            'ids.*'     => 'required|exists:site_sponsors,id|distinct'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }
        $this->service->multipleDelete($request);

        return response()->json([
            'status' => true,
        ]);
    }
}
