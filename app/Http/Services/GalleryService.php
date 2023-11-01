<?php

namespace App\Http\Services;

use App\Models\Gallery;
use App\Models\GalleryHasImage;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GalleryService
{
    protected $gallery;

    public function __construct(Gallery $gallery)
    {
        $this->gallery = $gallery;
    }

    public function storeGallery(Request $request): bool
    {
        DB::beginTransaction();

        try {
            $new_gallery = $this->gallery->clone()->create([
                'name' => $request->name,
                'user_id' => auth()->user()->id
            ]);

            foreach($request->images as $image) {
                $image_url = $new_gallery->images()->create([
                    'image_url' => ''
                ]);

                saveImage($image, '/uploads/galleries/', $image_url, 'image_url');
            }

            DB::commit();

            return true;
        } catch (QueryException $ex)
        {
            DB::rollback();
            Log::error('create gallery: ' . $ex->getMessage());
            return false;
        }
    }

    public function getAll(Request $request)
    {
        return [
            'public'  => $this->gallery->clone()
                            ->where('is_public', 1)
                            ->when($request->input('type') == 'public', function ($q) use ($request) {
                                return $q->where('name', 'like', $request->input('search').'%');
                            })
                            ->with(['user' => function($q) {
                                return $q->select('id','name','username')->withTrashed();
                            }])
                            ->withCount('images')
                            ->get(),
            'private' => $this->gallery->clone()
                            ->where('is_public', 0)
                            ->where('user_id', auth()->user()->id)
                            ->when($request->input('type') == 'private', function ($q) use ($request) {
                                return $q->where('name', 'like', $request->input('search').'%');
                            })
                            ->withCount('images')
                            ->get()
        ];
    }

    public function updateGalleryStatus($id): bool
    {
        $gallery = $this->gallery->clone()->findOrFail($id);

        if ($gallery->user_id != auth()->user()->id)
        {
            return false;
        }

        $gallery->is_public = !$gallery->is_public;
        $gallery->save();
        return true;
    }

    public function getImages($id)
    {
        return GalleryHasImage::where('gallery_id', $id)->latest()->paginate(1);
    }
}
