<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\SponsorRequest;
use App\Http\Services\SponsorService;
use App\Models\Sponsor;
use Illuminate\Http\Request;
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
        $data = $this->service->getAll();

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function store(SponsorRequest $request)
    {
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