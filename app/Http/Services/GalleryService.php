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

            if ($request->images) {
                foreach ($request->images as $image) {
                    $image_url = $new_gallery->images()->create([
                        'image_url' => ''
                    ]);

                    saveImage($image, '/uploads/galleries/', $image_url, 'image_url');
                }
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
        return GalleryHasImage::where('gallery_id', $id)->latest()->paginate(10);
    }

    public function updateInfo(Request $request, $id): ?string
    {
        $gallery = $this->gallery->clone()->findOrFail($id);

        if ($gallery->is_public == 0 && $gallery->user_id != auth()->user()->id)
        {
            return 'You are not allowed to update the name of a private folder.';
        }

        $gallery->update([
            'name' => $request->name
        ]);

        return null;
    }

    public function removeImage($id)
    {
        $img = GalleryHasImage::findOrFail($id);

        if ($img->gallery->is_public == 0 && $img->gallery->user_id != auth()->user()->id)
        {
            return 'you are not allowed to delete an image from a private folder.';
        }

        if ($img->usage > 0)
        {
            return 'Selected image is already in use.';
        }

        $img->delete();

        return null;
    }

    public function storeImages($request, $id)
    {
        $gallery = $this->gallery->clone()->findOrFail($id);

        if ($gallery->user_id != auth()->user()->id && $gallery->is_public == 0)
        {
            return 'You cannot add images to a private folder.';
        }

        DB::beginTransaction();

        try {
            foreach ($request->images as $image)
            {
                $image_url = $gallery->images()->create([
                    'image_url' => ''
                ]);

                saveImage($image, '/uploads/galleries/', $image_url, 'image_url');
            }

            DB::commit();

            return null;
        }
        catch (QueryException $ex)
        {
            DB::rollback();
            Log::error('add images to a folder: ' . $ex->getMessage());
            return $ex->getMessage();
        }
    }
}
