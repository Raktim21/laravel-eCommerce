<?php

namespace App\Http\Services;

use App\Models\Product;
use App\Models\ProductAbuseReport;
use App\Models\ProductImages;
use App\Models\ProductRestockRequest;
use App\Models\ProductReview;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Mews\Purifier\Facades\Purifier;

class ProductService
{

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getAll(Request $request, $isAdmin)
    {
        return $this->product->clone()
            ->when($isAdmin != 1, function ($q) {
                return $q->whereHas('inventories')->where('status', 1);
            })
            ->select('id', 'category_id', 'category_sub_id', 'brand_id',
                'uuid', 'name', 'short_description', 'display_price', 'previous_display_price', 'slug', 'thumbnail_image',
                'is_on_sale', 'is_featured', 'status')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->search . '%');
            })->when($request->category_id, function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })->when($request->sub_category_id, function ($query) use ($request) {
                return $query->where('category_sub_id', $request->sub_category_id);
            })->when($request->brands, function ($query) use ($request) {
                return $query->whereIn('brand_id', $request->brands);
            })->when($request->flash_sale == 1, function ($query) use ($request) {
                return $query->where('products.is_on_sale', 1);
            })->when($request->featured == 1, function ($query) use ($request) {
                return $query->where('products.is_featured', 1);
            })->when($request->discount_product == 1, function ($query) use ($request) {
                return $query->whereNotNull('previous_display_price');
            })->when($request->min_price && $request->max_price, function ($query) use ($request) {
                return $query->whereBetween('display_price', [$request->min_price, $request->max_price]);
            })->with(['category' => function ($q) {
                return $q->select('id', 'name');
            }])->with(['subCategory' => function ($q) {
                return $q->select('id', 'name');
            }])->with(['brand' => function ($q) {
                return $q->select('id', 'name');
            }])->with('productReviewRating')
            ->when($request->sort_by, function ($query) use ($request) {
                if ($request->sort_by == 'price_high_to_low') {
                    return $query->orderBy('display_price', 'desc');
                } else if ($request->sort_by == 'price_low_to_high') {
                    return $query->orderBy('display_price', 'asc');
                } else {
                    return $query->latest();
                }
            }, function ($query) {
                return $query->latest('products.id');
            })->paginate($request->per_page)->appends($request->except('page'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try{
            $product = $this->product->clone()->create([
                'uuid'                      => 'PR-' . uniqid(),
                'slug'                      => Str::slug($request->name).'-'.hexdec(uniqid()),
                'name'                      => $request->name,
                'description'               => Purifier::clean($request->description),
                'short_description'         => $request->short_description,
                'thumbnail_image'           => '',
                'brand_id'                  => $request->brand_id,
                'category_id'               => $request->category_id,
                'category_sub_id'           => $request->category_sub_id,
                'display_price'             => $request->display_price,
                'previous_display_price'    => $request->previous_display_price,
                'is_featured'               => $request->is_featured,
            ]);

            saveImage($request->file('thumbnail_image'), '/uploads/products/thumbnail/', $product, 'thumbnail_image');

            if ($request->is_featured == 1) {
                saveImage($request->file('featured_image'), '/uploads/products/featured_banner/', $product, 'featured_image');
            }

            if ($request->multiple_image && count($request->multiple_image) > 0)
            {
                $this->saveMultipleImage($request->multiple_image, $product->id);
            }

            DB::commit();
            Cache::clear();
            return $product->id;
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return 0;
        }
    }

    public function get($id)
    {
        return $this->product->clone()
            ->with(['category' => function($q) {
                return $q->select('id','name');
            }])
            ->with(['subCategory' => function($q) {
                return $q->select('id','name');
            }])
            ->with(['brand' => function($q) {
                return $q->select('id','name');
            }])
            ->with('productReviewRating')
            ->with('productImages','meta','productAttributes.attributeValues')
            ->with('defaultCombination.attributeValues')
            ->with(['productCombinations' => function ($q) {
                $q->with('attributeValues.attribute')
                ->with(['inventory' => function($q) {
                    return $q->when(auth()->guard('admin-api')->check(), function ($query) {
                        return $query->where('shop_branch_id', auth()->user()->shop_branch_id)
                            ->withTrashed();
                    });
                }])
                ->when(!auth()->guard('admin-api')->check(), function($q) {
                    return $q->where('is_active', 1);
                });
            }])
            ->withCount('requests')
            ->find($id);
    }

    public function update(Request $request, $id): void
    {
        $product = $this->product->clone()->findOrFail($id);

        $product->update([
            'name'                      => $request->name ?? $product->name,
            'slug'                      => Str::slug($request->name).'-'.hexdec(uniqid()) ?? $product->slug,
            'description'               => $request->description ?? $product->description,
            'short_description'         => $request->short_description ?? $product->short_description,
            'category_id'               => $request->category_id,
            'category_sub_id'           => $request->category_sub_id ?? $product->category_sub_id,
            'brand_id'                  => $request->brand_id ?? $product->brand_id,
            'is_on_sale'                => $request->is_on_sale ?? $product->is_on_sale,
            'is_featured'               => $request->is_featured ?? $product->is_featured,
            'status'                    => $request->status,
        ]);

        if ($request->is_featured == 1 && $request->hasFile('featured_image')) {
            deleteFile($product->featured_image);
            saveImage($request->file('featured_image'), '/uploads/products/featured_banner/', $product, 'featured_image');
        }

        if($request->hasFile('thumbnail_image')) {
            deleteFile($product->thumbnail_image);
            saveImage($request->file('thumbnail_image'), '/uploads/products/thumbnail/', $product, 'thumbnail_image');
        }
        Cache::clear();
    }

    public function delete($id)
    {
        $product = $this->product->clone()->findOrFail($id);

        $product->productAttributes()->delete();
        $product->requests()->delete();
        $product->productAbuseReports()->delete();
        $product->productCombinations()->each(function ($combo) {
            $combo->cart()->delete();
            $combo->wishlistItem()->delete();
            $combo->delete();
        });
        $product->inventories()->forceDelete();
        $product->delete();
        Cache::clear();
        return true;

    }

    public function multipleDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->ids as $item)
            {
                $this->delete($item);
            }
            DB::commit();
            Cache::clear();
            return true;
        }
        catch (QueryException $ex)
        {
            DB::rollback();
            return false;
        }
    }

    public function imageDelete($id): void
    {
        $image = ProductImages::find($id);
        deleteFile($image->image);
        $image->delete();
    }

    public function updateStatus($id)
    {
        $review = ProductReview::findOrFail($id);
        $status = $review->is_published == 1 ? 0 : 1;
        $review->is_published = $status;
        $review->save();
    }

    public function adminReply($reply, $id)
    {
        ProductReview::findOrFail($id)->update(['reply_from_merchant' => $reply]);
    }

    public function getAbuseReports()
    {
        return ProductAbuseReport::with(['user' => function($q) {
            $q->select('id','name','username','phone')->withTrashed();
        }])->with(['product' => function($q) {
            $q->select('id','name')->withTrashed();
        }])->latest()->paginate(15);
    }

    public function changeAbuseStatus($status, $id): void
    {
        ProductAbuseReport::findOrFail($id)->update(['is_checked' => $status]);
    }

    private function saveMultipleImage($images, $id): void
    {
        $product = $this->product->clone()->find($id);

        foreach ($images as $multiple_image)
        {
            $name = hexdec(uniqid()).'.'.$multiple_image->getClientOriginalExtension();
            $m_image = Image::make($multiple_image);
            $m_image->resize(600, 600);
            $m_image->save(public_path('/uploads/products/multiple_image/' . $name));
            $product->productImages()->create([
                'image' => '/uploads/products/multiple_image/'.$name,
            ]);
        }
    }

    public function getAllReviews()
    {
        return ProductReview::with('images')
            ->with(['orderItem' => function($q) {
                return $q->select('id','order_id','product_combination_id')
                    ->with(['order' => function($q) {
                        return $q->select('id','user_id','order_number')
                            ->with(['user' => function($q1) {
                                return $q1->select('id','username','name');
                            }]);
                    }])
                    ->with(['combination' => function($q1) {
                    return $q1->select()
                        ->with(['attributeValues' => function($q2) {
                        return $q2->withTrashed();
                    }])->with(['product' => function($q1) {
                        return $q1->select('id','name')->withTrashed();
                    }])->withTrashed();
                }]);
            }])
            ->latest()->paginate(10);
    }

    public function getReviewsByProduct($product_id)
    {
        return ProductReview::with('images')
            ->whereHas('orderItem', function ($q) use ($product_id) {
                return $q->whereHas('combination', function ($q1) use ($product_id) {
                    return $q1->where('product_id', $product_id);
                });
            })
            ->with(['orderItem' => function($q) {
                return $q->select('id','order_id','product_combination_id')
                    ->with(['order' => function($q) {
                        return $q->select('id','user_id','order_number')
                            ->with(['user' => function($q1) {
                                return $q1->select('id','username','name')->with('profile')->withTrashed();
                            }]);
                    }])
                    ->with(['combination' => function($q1) {
                        return $q1->select('id','product_id')
                            ->with(['attributeValues' => function($q2) {
                                return $q2->withTrashed();
                            }])->withTrashed();
                    }]);
            }])
            ->where('is_published', 1)
            ->paginate(5);
    }

    public function getReview($id)
    {
        return ProductReview::with('images')
            ->with(['orderItem' => function($q) {
                return $q->select('id','order_id','product_combination_id')
                    ->with(['order' => function($q) {
                        return $q->select('id','user_id','order_number')
                            ->with(['user' => function($q1) {
                                return $q1->select('id','username','name')->with('profile')->withTrashed();
                            }]);
                    }])
                    ->with(['combination' => function($q1) {
                        return $q1->select('id','product_id')
                            ->with(['attributeValues' => function($q2) {
                                return $q2->withTrashed();
                            }])->with(['product' => function($q1) {
                                return $q1->select('id','name')->withTrashed();
                            }])->withTrashed();
                    }]);
            }])
            ->find($id);
    }

    public function getAllRestock()
    {
        return ProductRestockRequest::selectRaw('product_id, COUNT(*) as request_count')
            ->where('is_stocked', 0)
            ->groupBy('product_id')
            ->with(['product' => function($q) {
                return $q->select('id','name','uuid','slug','thumbnail_image');
            }])
            ->get();
    }

    public function getProductByCategory($cat_id)
    {
        return $this->product->clone()
            ->whereHas('inventories')
            ->where('status', 1)
            ->where('category_id', $cat_id)
            ->select('id','category_id','category_sub_id','brand_id',
                'uuid','name','description','display_price','previous_display_price','slug','thumbnail_image')
            ->with(['category' => function($q) {
                return $q->select('id','name');
            }])->with(['subCategory' => function($q) {
                return $q->select('id','name');
            }])->with(['brand' => function($q) {
                return $q->select('id','name');
            }])
            ->latest()->take(5)->get();
    }

    public function getProductByUUID($uuid)
    {
        return $this->product->clone()
            ->where('uuid',$uuid)
            ->select('id','category_id','category_sub_id','brand_id',
                'uuid','name','description','display_price','previous_display_price','slug','thumbnail_image')
            ->with(['category' => function($q) {
                return $q->select('id','name');
            }])
            ->with(['subCategory' => function($q) {
                return $q->select('id','name');
            }])
            ->with(['brand' => function($q) {
                return $q->select('id','name');
            }])
            ->with('productReviewRating')
            ->with('productImages','productAttributes.attributeValues')
            ->with(['productCombinations' => function ($q) {
                return $q->with('attributeValues')
                    ->with('inventory');
            }])->first();
    }
}
