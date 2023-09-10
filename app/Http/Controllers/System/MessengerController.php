<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessengerOrderRequest;
use App\Http\Services\CategoryService;
use App\Http\Services\GeneralSettingService;
use App\Http\Services\OrderService;
use App\Http\Services\ProductService;
use App\Http\Services\PromoCodeService;
use App\Models\GeneralSetting;
use App\Models\MessengerSubscriptions;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PromoCode;
use App\Models\ShopReviews;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\UserProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MessengerController extends Controller
{

    public function productFilter()
    {
        $data = null;

        if(request()->has('id'))
        {
            $data = (new ProductService(new Product()))->get(request()->input('id'));
        }
        else if(request()->has('category_id')) {
            $data = (new ProductService(new Product()))->getProductByCategory(request()->input('category_id'));
        }
        else if(request()->has('uuid')) {
            $data = (new ProductService(new Product()))->getProductByUUID(request()->input('uuid'));
        }

        return response()->json([
            'status'  => true,
            'data'    => $data
        ], is_null($data) ? 204 : 200);
    }


    public function order(MessengerOrderRequest $request)
    {
        $status = (new OrderService(new Order()))->placeMessengerOrder($request);

        if($status == 'done')
        {
            return response()->json([
                'status' => true
            ], 201);
        }
        return response()->json([
            'status' => false,
            'errors' => $status
        ], 500);
    }


    public function getPromos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $user = User::where('username', $request->email)->first();

        if($user)
        {
            $data = (new PromoCodeService(new PromoCode()))->getAuthPromos($user->id);

            return response()->json([
                'status' => true,
                'data'   => $data
            ], count($data) == 0 ? 204 : 200);
        }

        return response()->json([
            'status' => false,
            'errors' => ['User account not found.']
        ], 404);
    }


    public function pendingOrders(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email'     => 'required|email|exists:users,username',
            'password'  => 'required|string'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        $user = User::where('username', $request->email)->first();

        if(Hash::check($request->password, $user->password)) {
            $orders = $user->orders()->whereNot('order_status_id', 4)
                ->select('id','order_number','total_amount')
                ->with(['items' => function($q) {
                    return $q->select('order_id','product_combination_id','product_quantity')
                        ->with(['combination' => function($q) {
                            return $q->with('attributeValues')->with(['product' => function($q1) {
                                return $q1->select('id','name')->withTrashed();
                            }])->withTrashed();
                        }]);
                }])->orderByDesc('id')->get();

            return response()->json([
                'status' => true,
                'data'   => $orders
            ], count($orders) === 0 ? 204 : 200);
        } else {
            return response()->json([
                'status' => false,
                'error'  => 'Password field is incorrect.'
            ], 401);
        }
    }

    public function cancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'messenger_psid'        => 'required|exists:user_profiles,messenger_psid',
            'order_number'          => 'required|exists:orders,order_number',
            'email'                 => 'required|email|exists:users,username'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status'    => false,
                'errors'    => $validator->errors()->all()
            ], 422);
        }

        $order = Order::where('order_number', $request->order_number)->first();
        $user = User::where('username', $request->email)->first();

        $msg = (new OrderService(new Order()))->cancelOrder($order, $user->id);

        if($msg == 'done')
        {
            return response()->json(['status' => true]);
        }

        $payload = array(
            'psid'          => $request->messenger_psid,
            'page_id'       => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
            'order_status'  => $msg,
            'order_no'      => $order->order_number
        );

        sendMessengerResponse($payload, 'order_status');

        return response()->json(['status' => true]);
    }

    public function subscribe(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'subscription_type' => 'required|array',
            'subscription_type.*' => 'required|in:1,2',
            'email'             => ['required','email',
                                    function($attr,$val,$fail) use ($request) {
                                        $user = User::where('username', $val)->first();

                                        if(is_null($user)) {
                                            $fail('User profile does not exist for the given email.');
                                        }
                                    }],
            'messenger_psid'    => 'required|string',
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        $user = User::where('username', $request->email)->first();

        foreach ($request->subscription_type as $type)
        {
            MessengerSubscriptions::firstOrCreate([
                'user_id'           => $user->id,
                'subscription_type_id' => $type,
            ]);
        }

        $user->profile->messenger_psid = $request->messenger_psid;
        $user->profile->save();

        return response()->json(['status' => true], 201);
    }

    public function storeReview(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'messenger_psid'     => 'required|exists:user_profiles,messenger_psid',
            'review'             => 'nullable|string|max:500',
            'rating'             => 'required|integer|in:1,2,3,4,5',
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        ShopReviews::create([
            'user_id'       => UserProfile::where('messenger_psid', $request->messenger_psid)->first()->user_id,
            'review'        => $request->review ?? 'No Comments',
            'rating'        => $request->rating,
        ]);

        return response()->json(['status' => true], 201);
    }

    public function getOrderStatus(Request $request)
    {
        $order = Order::where('order_number', $request->order_number)
            ->leftJoin('order_statuses','orders.order_status_id','=','order_statuses.id')
            ->select('orders.id','order_number','order_statuses.name as order_status','delivery_status')->first();

        $payload = array(
            'psid'          => $request->messenger_psid,
            'page_id'       => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
            'order_status'  => is_null($order) ? 'Not Found' : ($order->delivery_status ?? $order->order_status),
            'order_no'      => $request->order_number
        );

        sendMessengerResponse($payload, 'order_status');

        return response()->json(['status' => true]);
    }

    public function invoicePDF(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->order_id);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'error' => 'Bad Request'], 400);
        }

        $order = (new OrderService(new Order()))->getData($id);

        if(is_null($order))
        {
            return response()->json([
                'status' => false,
                'error'  => 'Invalid order ID'
            ], 400);
        }

        $general = (new GeneralSettingService(new GeneralSetting()))->getSetting();

        $data = array(
            'order' => $order,
            'general' => $general,
            'title' => $order->order_number
        );

        $pdf = PDF::loadView('invoice', $data);

        return $pdf->stream('invoice_'. $order->order_number .'.pdf');
    }

    public function category()
    {
        $data = Cache::remember('allCategories', 60*60, function () {
            return (new CategoryService(new ProductCategory()))->getAll(false, false);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }
}
