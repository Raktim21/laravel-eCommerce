<?php

namespace App\Http\Services;

use App\Models\ProductSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SubCategoryService
{
    protected $sub_category;

    public function __construct(ProductSubCategory $sub_category)
    {
        $this->sub_category = $sub_category;
    }

    public function getAll()
    {
        return $this->sub_category->clone()->latest()->get();
    }

    public function read($id)
    {
        return $this->sub_category->clone()->findOrFail($id);
    }

    public function store(Request $request)
    {
        $subCat = $this->sub_category->clone()->create([
            'name'              => $request->name,
            'slug'              => Str::slug($request->name).'-'.uniqid(),
            'category_id'       => $request->category_id
        ]);

        if($request->hasFile('image'))
        {
            saveImage($request->file('image'), '/uploads/images/sub_categories/', $subCat, 'image');
        }

        Cache::delete('sub_categories');
        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function update(Request $request, $id)
    {
        $subCat = $this->sub_category->clone()->findOrFail($id);

        $subCat->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.uniqid(),
            'category_id' => $request->category_id
        ]);

        if($request->hasFile('image'))
        {
            deleteFile($subCat->image);
            saveImage($request->file('image'), '/uploads/images/sub_categories/', $subCat, 'image');
        }
        Cache::delete('sub_categories');
        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function delete($id)
    {
        $this->sub_category->clone()->findOrFail($id)->delete();
        Cache::delete('sub_categories');
        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function multipleDelete(Request $request)
    {
        $this->sub_category->clone()->whereIn('id',$request->ids)->delete();
        Cache::delete('sub_categories');
        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function getSubCategories($category_id)
    {
        return $this->sub_category->clone()->where('category_id', $category_id)->get();
    }

}
