<?php

namespace App\Http\Controllers\Admin\Analytics;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sponsor;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use App\Models\ProductSubCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{

//    admin dashboard charts and analytics (cached for 10 minutes)

    public function index()
    {
        $data = Cache::remember('adminDashboardData', 60*10, function () {

            $admin_model     = User::query()->role(['Super Admin', 'Merchant']);

            \Config::set('auth.defaults.guard','user-api');
            $user_model      = User::query()->role('Customer');
            \Config::set('auth.defaults.guard','admin-api');

            $order_model     = Order::query();
            $product_model   = Product::query();

            $admin_count     = $admin_model->clone()->count();
            $user_count      = $user_model->clone()->count();
            $order_count     = $order_model->clone()->count();
            $product_count   = $product_model->clone()->count();

//            5 recently joined users

            $recent_users    = $user_model->clone()
                                          ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                                          ->leftJoin('user_sexes', 'user_profiles.user_sex_id', '=', 'user_sexes.id')
                                          ->select('users.id as id','users.name as name','users.username as email','users.phone as phone','user_sexes.name as gender','user_profiles.image as avatar')
                                          ->latest('users.created_at')->take(5)
                                          ->get();

//            5 recently placed orders

            $recent_orders   = $order_model->clone()
                                ->leftJoin('order_statuses' , 'orders.order_status_id', '=', 'order_statuses.id')
                                ->leftJoin('user_addresses', 'orders.delivery_address_id', '=', 'user_addresses.id')
                                ->select('orders.id as id','orders.user_id as user_id','orders.order_number as order_number',
                                    'order_statuses.name as admin_status','orders.total_amount as total','user_addresses.phone_no as shipping_number')
                                ->latest('orders.created_at')->take(5)->get();

//            5 recently added products

            $recent_products = $product_model->clone()
                                        ->with(['category' => function($q) {
                                            return $q->select('id','name');
                                        }])->with(['subCategory' => function($q) {
                                            return $q->select('id','name');
                                        }])
                                        ->select('id','category_id','category_sub_id','name','slug','thumbnail_image','view_count')
                                        ->latest()->take(5)->get();

//            admins

            $recent_admins   = $admin_model->clone()
                                        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                                        ->select('users.id as id','name','username as email','phone','user_profiles.image as avatar')
                                        ->latest('users.created_at')->take(5)
                                        ->get();

//            sales of top 3 categories, orders and monthly new user count in the following year

            $category        =  ProductCategory::join('products', 'product_categories.id', '=', 'products.category_id')
                                        ->leftjoin('product_combinations', 'products.id', '=', 'product_combinations.product_id')
                                        ->leftjoin('order_items', 'product_combinations.id', '=', 'order_items.product_combination_id')
                                        ->groupBy('product_categories.id', 'product_categories.name')
                                        ->select('product_categories.id', 'product_categories.name', DB::raw('SUM(order_items.product_quantity) as total'))
                                        ->orderByDesc('total')
                                        ->limit(3)
                                        ->get();

            $top_categories = [];

            foreach ($category as $cat) {
                $top_categories [] = $cat->name;
            }

            $top_product_category_count = [];

            for ($i=0; $i < 12; $i++) {

                $date      = Carbon::now()->subMonths($i);
                $startDate = $date->clone()->startOfMonth();
                $endDate   = $date->clone()->endOfMonth();


                $user_month_count [$date->format('M, Y')]  = $user_model->clone()->whereBetween('created_at', [$startDate, $endDate])->count();

                $order_month_count [$date->format('M, Y')] = $order_model->clone()->whereBetween('created_at', [$startDate, $endDate])->count();

                foreach ( $category  as $cat) {

                    $top_product_category_count[$cat->name][$date->format('M, Y')]  =  DB::table('product_categories')->join('products', 'product_categories.id', '=', 'products.category_id')
                                                                                           ->join('product_combinations','products.id','=','product_combinations.product_id')
                                                                                           ->join('order_items', 'order_items.product_combination_id', '=', 'product_combinations.id')
                                                                                           ->whereBetween('order_items.created_at', [$startDate, $endDate])
                                                                                           ->where('product_categories.id', '=', $cat->id)
                                                                                           ->sum('order_items.product_quantity');
                }

            }

            return [
                'admin_count'                => $admin_count,
                'user_count'                 => $user_count,
                'order_count'                => $order_count,
                'product_count'              => $product_count,
                'recent_users'               => $recent_users,
                'recent_orders'              => $recent_orders,
                'recent_products'            => $recent_products,
                'recent_admins'              => $recent_admins,
                'user_month_count'           => $user_month_count,
                'order_month_count'          => $order_month_count,
                'top_product_category_count' => $top_product_category_count,
                'top_categories'             => $top_categories,
                'category_count'             => ProductCategory::count(),
                'sub_category_count'         => ProductSubCategory::count(),
                'brand_count'                => ProductBrand::count(),
                'sponsor_count'              => Sponsor::count(),
            ];
        });


        return response()->json([
            'status'    => true,
            'data'       => $data,
        ], is_null($data) ? 204 : 200);
    }

//    pending orders count (cached for 5 minutes)

    public function pending_order_count()
    {
        $data = Cache::remember('pendingOrders', 60*5, function () {
            return Order::where('order_status_id', 1)->count();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data,
        ]);
    }
}
