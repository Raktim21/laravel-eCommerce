<?php

namespace App\Http\Controllers;

use App\Http\Requests\GalleryCreateRequest;
use App\Http\Services\GalleryService;
use App\Models\Gallery;
use App\Models\GalleryHasImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    protected $service;

    public function __construct(GalleryService $service)
    {
        $this->service = $service;
    }

    public function galleryList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'      => 'required_with:search|in:public,private',
            'search'    => 'required_with:type|string'
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $data = $this->service->getAll($request);

        return response()->json([
            'status' => true,
            'data'   => $data
        ], (count($data['public']) == 0 && count($data['private']) == 0) ? 204 : 200);
    }

    public function galleryImages($id)
    {
        $gallery = Gallery::findOrFail($id);

        if ($gallery->is_public == 0 && $gallery->user_id != auth()->user()->id)
        {
            return response()->json(['status' => false], 204);
        }

        $data = Cache::remember('gallery'.$id.request()->get('page', 1), 24*60*60, function () use ($id) {
            return $this->service->getImages($id);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function create(GalleryCreateRequest $request)
    {
        if($this->service->storeGallery($request))
        {
            return response()->json(['status' => true], 201);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Something went wrong.']
        ], 500);
    }

    public function addImages(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'images'    => 'required|array',
            'images.*'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        if($response = $this->service->storeImages($request, $id))
        {
            return response()->json([
                'status' => false,
                'errors' => [$response]
            ], 400);
        }

        return response()->json(['status' => true], 201);
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:100'
        ]);

        if ($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        if ($response = $this->service->updateInfo($request, $id))
        {
            return response()->json([
                'status' => false,
                'errors' => [$response]
            ], 400);
        }

        return response()->json(['status' => true]);
    }

    public function updateStatus($id)
    {
        if ($this->service->updateGalleryStatus($id))
        {
            return response()->json(['status' => true]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['You are not authorized to update the status.']
        ], 403);
    }

    public function deleteImage($id)
    {
        if ($response = $this->service->removeImage($id))
        {
            return response()->json([
                'status' => false,
                'errors' => [$response]
            ], 400);
        }

        return response()->json(['status' => true]);
    }
}
