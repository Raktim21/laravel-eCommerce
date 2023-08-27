<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Requests\HomepageRequest;
use App\Http\Requests\ProductAbuseReportRequest;
use App\Http\Requests\RestockRequest;
use App\Http\Requests\SubscriberRequest;
use App\Http\Services\AssetService;
use App\Http\Services\BrandService;
use App\Http\Services\CategoryService;
use App\Http\Services\ContactService;
use App\Http\Services\FlashSaleService;
use App\Http\Services\GeneralSettingService;
use App\Http\Services\ProductService;
use App\Http\Services\SubCategoryService;
use App\Http\Services\SubscriberService;
use App\Models\BannerSetting;
use App\Models\Contact;
use App\Models\FlashSale;
use App\Models\GeneralSetting;
use App\Models\OrderAdditionalCharge;
use App\Models\OrderDeliveryMethod;
use App\Models\OrderPaymentMethod;
use App\Models\Product;
use App\Models\ProductAbuseReport;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductRestockRequest;
use App\Models\ProductSubCategory;
use App\Models\SiteBanners;
use App\Models\Sponsor;
use App\Models\StaticMenu;
use App\Models\Subscriber;
use App\Models\ThemeCustomizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{

    public function general()
    {
        $data = Cache::remember('general', 24*60*60, function () {
            return (new GeneralSettingService(new GeneralSetting()))->getSetting();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], is_null($data) ? 204 : 200);
    }

    public function theme()
    {
        $data = Cache::remember('theme', 60*60, function () {
            return ThemeCustomizer::orderBy('ordering')->get();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], is_null($data) ? 204 : 200);
    }


    public function home()
    {
        $theme = Cache::remember('themeCustomizer', 60*60*24, function () {
            return ThemeCustomizer::get();
        });

        $data = array();

        $data['site_banners'] = Cache::remember('siteBanners', 60*60*24, function () {
            return SiteBanners::first();
        });

        if($theme[1]['is_active'] == 1) {
            $data['banners'] = Cache::remember('allBanner', 60*60*24, function () {
                return BannerSetting::latest()->get();
            });
        }

        $data['categories'] = Cache::remember('allCategory', 60*60*24, function () {
            return (new CategoryService(new ProductCategory()))->getAll(0, false);
        });

        $data['flash_sale'] = Cache::remember('flash_sale', 60*60*24, function () {
            return FlashSale::where('status', 1)->first();
        });

        if($theme[3]['is_active'] == 1) {
            $data['featured_products'] = Cache::remember('allProductsFeatured', 60*60*24, function () {
                return Product::whereHas('productCombinations.inventory')->where('is_featured', 1)->where('status', 1)
                    ->select('id','category_id','category_sub_id','description','name','slug','uuid','thumbnail_image',
                        'display_price','previous_display_price','view_count')
                    ->with('productReviewRating')
                    ->with('subCategory','category')
                    ->inRandomOrder()->take(8)->get();
            });
        }

        if($theme[4]['is_active'] == 1 && FlashSale::first() != null && FlashSale::first()->status == 1) {
            $data['sale_products'] = Cache::remember('productOnSale', 60*60*24, function () {
                return  Product::whereHas('inventories')->where('is_on_sale',1)->where('status', 1)
                    ->select('id','category_id','category_sub_id','description','name','slug','uuid','thumbnail_image',
                        'display_price','previous_display_price','view_count')
                    ->with('productReviewRating')
                    ->with('subCategory','category')
                    ->latest()->take(20)->get();
            });
        }

        if($theme[6]['is_active'] == 1) {
            $data['new_products'] = Cache::remember('productsNew', 60*60*24, function () {

                return Product::whereHas('inventories')->where('status', 1)
                    ->select('id','category_id','category_sub_id','description','name','slug','uuid','thumbnail_image',
                        'display_price','previous_display_price','view_count')
                    ->with('productReviewRating')
                    ->with('subCategory','category')
                    ->latest()->take(12)->get();
            });
        }

        if($theme[7]['is_active'] == 1) {
            $data['discount_products'] = Cache::remember('productDiscount', 60*60*24, function () {

                return Product::whereHas('inventories')->where('status', 1)->whereNotNull('previous_display_price')
                    ->select('id','category_id','category_sub_id','description','name','slug','uuid','thumbnail_image',
                        'display_price','previous_display_price','view_count')
                    ->with('productReviewRating')
                    ->with('subCategory','category')
                    ->latest()->take(16)->get();
            });
        }

        if($theme[8]['is_active'] == 1) {
            $data['sponsors'] = Cache::remember('sponsors', 60*60*24, function () {
                return Sponsor::all();
            });
        }

        if($theme[9]['is_active'] == 1) {
            $data['popular_products'] = Cache::remember('productPopular', 60*60*24, function () {
                return Product::whereHas('inventories')->where('status', 1)
                    ->select('id','category_id','category_sub_id','description','name','slug','uuid','thumbnail_image',
                        'display_price','previous_display_price','view_count')
                    ->with('productReviewRating')
                    ->with('subCategory','category')
                    ->orderByDesc('sold_count')->take(12)->get();
            });
        }

        return response()->json([
            'status'    => true,
            'data'      => $data
        ]);
    }



    public function staticMenu(){
        $data = StaticMenu::with('staticMenuType')->latest()->get();

        return response()->json([
            'status' => true,
            'data'   => $data
         ], $data->isEmpty() ? 204 : 200);
    }


    public function getBanners()
    {
        $data = Cache::remember('siteBanners', 60*60*24, function () {
            return SiteBanners::first();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], is_null($data) ? 204 : 200);
    }


    public function staticMenuContent($id){
        $data = StaticMenu::with('staticContent')->find($id);

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function category()
    {
        $data = Cache::remember('allCategories', 60*60, function () {
            return (new CategoryService(new ProductCategory()))->getAll(1, false);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }

    public function getSubCategoryList($category_id): \Illuminate\Http\JsonResponse
    {
        $data = (new SubCategoryService(new ProductSubCategory()))->getSubCategories($category_id);

        return response()->json([
            'status'        => true,
            'data'          => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function brand()
    {
        $data = Cache::remember('allBrands', 60*60, function () {
            return (new BrandService(new ProductBrand()))->getAll(false);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }

    public function productFilter(HomepageRequest $request)
    {
        $data = (new ProductService(new Product()))->getAll($request, 0);

        return response()->json([
            'status'   => true,
            'data'     => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function productReviews($product_id)
    {
        $data = (new ProductService(new Product()))->getReviewsByProduct($product_id);

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], $data->isEmpty() ? 204 : 200);
    }



    public function productSearchSuggestions()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $data = Product::where('name', 'like', '%'.request()->name.'%')->take(5)->get(['id', 'name']);

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function productDetails($id)
    {
        $data = (new ProductService(new Product()))->get($id);

        return response()->json([
            'status' => true,
            'data' => $data,
        ], is_null($data) ? 204 : 200);
    }


    public function paymentMethods()
    {
        $data = Cache::remember('paymentMethods', 60*60*24, function () {
            return OrderPaymentMethod::where('is_active',1)->latest()->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }

    public function additionalCharges()
    {
        $data = Cache::remember('additionalCharges', 24*60*60, function () {
            return OrderAdditionalCharge::where('status', 1)->get();
        });

        return response()->json([
            'status'        => true,
            'data'          => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function deliveryMethods()
    {
        $data = Cache::remember('shippingMethods', 60*60*24, function () {
            return OrderDeliveryMethod::where('is_active',1)->latest()->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }


    public function subscribe(SubscriberRequest $request)
    {
        (new SubscriberService(new Subscriber()))->store($request);

        return response()->json([
            'status' => true,
        ],201);
    }


    public function contact(ContactRequest $request)
    {
        (new ContactService(new Contact()))->store($request);

        return response()->json([
            'status' => true,
        ], 201);
    }

    public function restockRequest(RestockRequest $request)
    {
        ProductRestockRequest::create([
            'user_id'       => auth()->guard('user-api')->user()->id,
            'product_id'    => $request->product_id,
        ]);
        return response()->json(['status' => true], 201);
    }

    public function reportProduct(ProductAbuseReportRequest $request)
    {
        ProductAbuseReport::create([
            'user_id'       => auth()->guard('user-api')->check() ? auth()->user()->id : null,
            'guest_session_id' => uniqid('GUEST',),
            'email'             => $request->email,
            'phone_no'          => $request->phone_no,
            'product_id'        => $request->product_id,
            'complaint_notes'   => $request->complaint_notes,
        ]);

        return response()->json(['status' => true], 201);
    }

    public function faqList()
    {
        $data = Cache::remember('faqs', 24*60*60, function () {
            return (new AssetService())->getFaqs();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function flashSale()
    {
        $data = (new FlashSaleService())->getSale();

        return response()->json([
            'status' => true,
            'data'   => $data
        ],$data['flash_sale'] == null ? 204 : 200);
    }
}