<?php

namespace App\Http\Services;

use App\Models\BannerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BannerService
{

    protected $banner;

    public function __construct(BannerSetting $banner)
    {
        $this->banner = $banner;
    }

    public function getAll()
    {
        return $this->banner->clone()->latest()->get();
    }

    public function read($id)
    {
        return $this->banner->clone()->find($id);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $banner = $this->banner->clone()->create($request->except('image'));

            saveImage($request->file('image'), '/uploads/images/banner/', $banner, 'image');

            DB::commit();
            return true;
        }
        catch (\Throwable $th)
        {
            return false;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $banner = $this->banner->clone()->findOrFail($id);

            deleteFile($banner->image);

            $banner->update($request->except('image'));

            saveImage($request->image, '/uploads/images/banner/', $banner, 'image');

            DB::commit();

            return true;
        } catch (\Throwable $th)
        {
            return false;
        }
    }

    public function delete($id)
    {
        $banner = $this->banner->clone()->findOrFail($id);
        deleteFile($banner->image);
        $banner->delete();
    }

    public function multipleDeletes(Request $request)
    {
        $rows = $this->banner->clone()->whereIn('id',$request->ids)->get();

        foreach($rows as $row)
        {
            deleteFile($row->image);
        }

        $this->banner->clone()->whereIn('id',$request->ids)->delete();
    }

}
