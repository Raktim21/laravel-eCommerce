<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportService
{

    public function getGeneralData()
    {
        $status = OrderStatus::where('name', 'Delivered')->first();
        $data = DB::table('orders')->where('order_status_id','=',$status->id)
            ->selectRaw(
                "count(id) as total_order,
                        round(sum(total_amount),2) as revenue,
                        (select sum(product_combinations.cost_price) as product_cost from
                        order_items left join product_combinations on
                        order_items.product_combination_id=product_combinations.id left join orders on
                        order_items.order_id=orders.id where
                        orders.order_status_id='".$status->id."') as product_cost"
            )->first();

        $data->cost            = DB::table('expenses')->sum('amount');

        $data->revenue         = $data->revenue == null ? 0 : $data->revenue; 
        $data->product_cost    = $data->product_cost == null ? 0 : $data->product_cost; 

        $data->gross_profit    = round(($data->revenue - $data->product_cost),2);

        $data->net_profit      = round(($data->gross_profit - $data->cost),2);

        if($data->total_order == 0)
        {
            $data->avg_order_value = 0;
        }
        else
        {
            $data->avg_order_value = round(($data->revenue / $data->total_order),2);

        }

        return $data;
    }

    public function getProducts()
    {
        return DB::table('products')->select('name','view_count')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();
    }

    public function getUsers($year)
    {
        return DB::table('users')
            ->selectRaw('monthname(created_at) as month_name, count(id) as total_user')
            ->whereRaw('year(created_at)='.$year)
            ->groupByRaw('month(created_at),monthname(created_at)')
            ->get();

    }

    public function getMostActiveUsers(Request $request)
    {
        $status = OrderStatus::where('name', 'Delivered')->first();
        return DB::table('orders')
            ->where('order_status_id','=',$status->id)
            ->when($request->start_date != null && $request->end_date != null, function ($query) use ($request) {
                $query->whereBetween('orders.created_at', [$request->start_date, $request->end_date]);
            })
            ->leftJoin('users','orders.user_id','=','users.id')
            ->groupBy('orders.user_id','users.name','users.username')
            ->selectRaw('orders.user_id,users.name,users.username,
                                count(*) as num_orders,
                                round(sum(orders.total_amount),2) as total_amount'
            )
            ->orderByDesc('num_orders')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();
    }

    public function soldProducts(Request $request)
    {
        return DB::table('order_items')
            ->when($request->start_date != null && $request->end_date != null, function ($query) use ($request) {
                $query->whereBetween('order_items.created_at', [$request->start_date, $request->end_date]);
            })
            ->groupBy('product_combinations.product_id','products.name')
            ->leftJoin('product_combinations','order_items.product_combination_id','=','product_combinations.id')
            ->leftJoin('products','product_combinations.product_id','=','products.id')
            ->selectRaw('product_combinations.product_id,products.name,
                                sum(order_items.product_quantity) as total_quantity,sum(round(order_items.total_price,2)) as total_amount,
                                count(*) as num_orders'
            )
            ->orderByDesc('num_orders')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }

    public function productData(Request $request, $product_id)
    {
        $sales_data = DB::table('order_items')
            ->when($request->start_date != null && $request->end_date != null, function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })
            ->selectRaw(
                "(select count(*) from orders inner join order_items on orders.id=order_items.order_id
                                        inner join product_combinations on order_items.product_combination_id=product_combinations.id
                                        where orders.admin_status='Cancelled' and product_combinations.product_id=". $product_id .") as cancelled_orders,
                                        count(*) as num_orders,
                                        sum(round(order_items.total,2)) as total_amount,
                                        sum(order_items.quantity) as no_items,
                                        (select user_addresses.shipping_division_id from orders left join user_addresses on orders.shipping_address_id=user_addresses.id
                                        left join order_items on orders.id=order_items.order_id
                                        where order_items.product_id=99
                                        group by division order by count(*) desc limit 1) as most_sold_division"
            )->where('product_id','=',$product_id)->first();

        $product_data = Product::with('productImages')
            ->select('id','name','description','short_description','thumbnail','price','discount_amount')
            ->find($product_id);

        $division_data = DB::table('orders')
            ->when($request->start_date != null && $request->end_date != null, function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })
            ->leftJoin('order_items','orders.id','=','order_items.order_id')
            ->selectRaw('count(*) as total_orders, shipping_divission')
            ->where('product_id','=',$product_id)
            ->groupBy('shipping_divission')
            ->orderBy('total_orders','desc')
            ->limit(4)
            ->get();

        return array(
            'product_data' => $product_data,
            'sales_data' => $sales_data,
            'sales_division_data' => $division_data
        );
    }

    public function sales($year)
    {
        return DB::table('orders')
            ->selectRaw('monthname(created_at) as month_name,round(sum(total_amount)) as total_sold')
            ->whereRaw('year(created_at)='.$year)
            ->groupBy(
                DB::raw('month(created_at)'),
                DB::raw('monthname(created_at)')
            )
            ->get();
    }

    public function orderedCategories(Request $request)
    {
        return DB::table('order_items')
            ->when($request->start_date != null && $request->end_date != null, function ($query) use ($request) {
                $query->whereBetween('order_items.created_at', [$request->start_date, $request->end_date]);
            })
            ->leftJoin('product_combinations','order_items.product_combination_id','=','product_combinations.id')
            ->leftJoin('products','product_combinations.product_id','=','products.id')
            ->leftJoin('product_categories','products.category_id','=','product_categories.id')
            ->selectRaw('product_categories.id,product_categories.name,sum(order_items.product_quantity) as total_orders')
            ->groupBy('product_categories.id','product_categories.name')
            ->orderByDesc('total_orders')
            ->get();
    }

    public function getOrder($order_id)
    {
        return Order::with('items','user')->with(['items.product' => function($q) {
            $q->select('id','name');
        }])->findOrFail($order_id);
    }


}
