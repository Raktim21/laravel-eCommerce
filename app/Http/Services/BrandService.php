<?php

namespace App\Http\Services;

use App\Models\ProductBrand;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BrandService
{
    protected $brand;

    public function __construct(ProductBrand $brand)
    {
        $this->brand = $brand;
    }

    public function getAll($isPaginated)
    {
        if($isPaginated)
        {
            return $this->brand->clone()->latest()->paginate(10);
        }
        return $this->brand->clone()->latest()->get();
    }


    public function store(Request $request)
    {
        $brand = $this->brand->clone()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.Str::random(5)
        ]);

        if($request->hasFile('image'))
        {
            saveImage($request->file('image'), '/uploads/images/brands/', $brand, 'image');
        }

        Cache::forget('brands');
    }


    public function update(Request $request, $id)
    {
        $brand = $this->brand->clone()->findOrFail($id);

        $brand->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.Str::random(5)
        ]);

        if($request->hasFile('image'))
        {
            deleteFile($brand->image);
            saveImage($request->file('image'), '/uploads/images/brands/', $brand, 'image');
        }

        Cache::forget('brands');
    }


    public function delete($id)
    {
        $brand = $this->brand->clone()->findOrFail($id);

        try {
            $brand->delete();
            deleteFile($brand->image);
            Cache::forget('brands');
            return true;
        }
        catch (QueryException $e)
        {
            return false;
        }
    }



    public function multipleDelete(Request $request)
    {
        $rows = $this->brand->clone()->whereIn('id',$request->ids)->get();

        foreach($rows as $row)
        {
            deleteFile($row->image);
        }

        $this->brand->clone()->whereIn('id',$request->ids)->delete();
        Cache::forget('brands');
    }
}
