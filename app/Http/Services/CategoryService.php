<?php

namespace App\Http\Services;

use App\Models\ProductCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryService
{
    protected $category;

    public function __construct(ProductCategory $category)
    {
        $this->category = $category;
    }

    public function getAll($isPaginated, $isAdmin)
    {
        if($isPaginated==1)
        {
            return $this->category->clone()
                ->when(!$isAdmin || \request()->input('status') == 1, function ($q) {
                    return $q->where('status', 1);
                })
                ->when(request()->input('search'), function ($q) {
                    return $q->where('name', 'LIKE', '%'.request()->input('search').'%')
                        ->orWhereHas('subCategories', function ($query) {
                            $query->where('name', 'LIKE', '%'.request()->input('search').'%');
                        });
                })
                ->orderBy('ordering')
                ->with('subCategories')
                ->withCount('products')
                ->paginate(35)->appends(request()->except('page'));
        }
        return $this->category->clone()->when(!$isAdmin || \request()->input('status') == 1, function ($q) {
            return $q->where('status', 1);
        })->with('subCategories')->orderBy('ordering')->get();
    }

    public function get($id, $getSubCat)
    {
        if($getSubCat)
        {
            return $this->category->clone()->find($id)->subCategories;
        }
        return $this->category->clone()->find($id);
    }

    public function store(Request $request): void
    {
        $category = $this->category->clone()->create([
            'name'    => $request->name,
            'slug'    => Str::slug($request->name).'-'.hexdec(uniqid()),
            'image'   => '',
            'ordering'=> $this->category->clone()->count() + 1,
        ]);

        saveImage($request->file('image'), '/uploads/images/category/', $category, 'image');

        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function update(Request $request, $id): void
    {
        $category = $this->category->clone()->findOrFail($id);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.hexdec(uniqid())
        ]);

        if($request->hasFile('image'))
        {
            deleteFile($category->image);

            saveImage($request->file('image'), '/uploads/images/category/', $category, 'image');
        }

        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function delete($id): bool
    {
        $category = $this->category->clone()->findOrFail($id);

        try {
            $category->delete();
            deleteFile($category->image);
            Cache::delete('allCategories');
            Cache::delete('allCategory');
            Cache::delete('categories');
            return true;
        } catch (QueryException $e)
        {
            return false;
        }
    }

    public function shuffleCategories(Request $request): void
    {
        foreach($request->categories as $key => $category){

            $this->category->clone()->findOrFail($category)->update([
                'ordering' => $key + 1,
            ]);
        }
        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function deleteCategories(Request $request): void
    {
        $rows = $this->category->clone()->whereIn('id',$request->ids)->get();

        foreach($rows as $row)
        {
            deleteFile($row->image);
        }

        $this->category->clone()->whereIn('id',$request->ids)->delete();
        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

    public function changeStatus($id): void
    {
        $cat = $this->category->clone()->findOrFail($id);

        $status = $cat->status == 1 ? 0 : 1;

        $cat->update(['status' => $status]);

        Cache::delete('allCategories');
        Cache::delete('allCategory');
        Cache::delete('categories');
    }

}
