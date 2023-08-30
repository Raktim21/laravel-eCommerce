<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderSearchRequest;
use App\Http\Services\OrderService;
use App\Models\OrderDeliveryMethod;
use App\Models\OrderPaymentMethod;
use App\Models\OrderPickupAddress;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Http\Requests\AdminOrderStatusChangeRequest;
use App\Http\Requests\SalesRequest;
use App\Models\GeneralSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }


    public function index(OrderSearchRequest $request): \Illuminate\Http\JsonResponse
    {
        $order = $this->service->getOrderList(false);

        return response()->json([
            'status' => true,
            'data'    => $order
        ], $order->isEmpty() ? 204 : 200);
    }


    public function adminOrder(OrderSearchRequest $request): \Illuminate\Http\JsonResponse
    {
        $order = $this->service->getOrderList(true);

        return response()->json([
            'status'  => true,
            'data'    => $order
        ], $order->isEmpty() ? 204 : 200);
    }


    public function sales(SalesRequest $request): \Illuminate\Http\JsonResponse
    {
        $status = $this->service->placePOSOrder($request);

        if($status == 1) {
            return response()->json([
                'status'    => false,
                'errors'    => ['Something went wrong.']
            ], 500);
        } else if($status == 2) {
            return response()->json([
                'status'    => false,
                'errors'    => ['You can not place an order that weighs over 5kg.']
            ], 422);
        } else {
            return response()->json([
                'status'    => true,
                'data'      => array('order_number' => $status)
            ], 201);
        }
    }


    public function getDeliveryCost(): \Illuminate\Http\JsonResponse
    {
        $validate = Validator::make(request()->all(), [
            'user_address_id' => 'required|exists:user_addresses,id',
            'total_price'     => 'required|numeric'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }
        $delivery_charge = getDeliveryCharge(request()->input('user_address_id'),request()->input('total_price'));

        return response()->json([
            'status' => true,
            'data'    => array(
                'delivery_charge' => $delivery_charge
            ),
        ]);

    }


    public function detail($id): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('orderDetail'.$id, 24*60*60, function () use ($id) {
            return $this->service->getData($id);
        });

        return response()->json([
                'status' => true,
                'data' => $data
            ], is_null($data) ? 204 : 200);
    }


    public function changeStatus(AdminOrderStatusChangeRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        $order = Order::findOrfail($id);

        if($order->order_status_id == 3)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Order status cannot be changed after being cancelled.']
            ], 400);
        }

        if($order->delivery_status == 'Picked')
        {
            return response()->json([
                'status' => false,
                'errors' => ['Order status cannot be changed after being picked.']
            ], 400);
        }

        $status = OrderStatus::findOrFail($request->status);

        if ($order->order_status_id > $status->id) {
            return response()->json([
                'status' => false,
                'errors' => ['Order status cannot be changed to '. $status->name .' when the current status is '. $order->status->name .'.']
            ], 400);
        }

        $pickup = OrderPickupAddress::first();

        if ($pickup == null) {
            return response()->json([
                'status' => false,
                'errors' => ['Please add pickup address first.']
            ], 400);
        }

        if($request->status == 2 && !$this->service->checkEligibility($order, $request->shop_branch_id))
        {
            return response()->json([
                'status'    => false,
                'errors'    => ['Some of the order items are not available in the selected branch.']
            ], 400);
        }


        if (GeneralSetting::first()->delivery_status == 1) {
            if($request->status == 2) {
               $weight = $this->service->getOrderWeight($order);
               $this->service->paperFlyOrder($order, $weight);
            } else {
                if ($request->status == 3 && $order->delivery_tracking_number != null) {
                    $this->service->paperFlyCancelOrder($order);
                }
            }
        }
        if($request->status == 3)
        {
            $order->delivery_status = 'Cancelled';
        }
        $order->order_status_id = $request->status;

        if(is_null($order->shop_branch_id))
        {
            $order->shop_branch_id  = $request->shop_branch_id;
        }

        $order->merchant_remarks = $request->merchant_remarks ?? null;

        $order->save();

        return response()->json([
            'status' => true,
        ]);
    }


    public function changeNote(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $order = Order::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'merchant_remarks' => 'required|string|min:4'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        $order->update([
            'merchant_remarks' => $request->merchant_remarks
        ]);

        return response()->json([
            'status' => true,
        ]);
    }


    public function paymentMethodList(): \Illuminate\Http\JsonResponse
    {
        $data = Cache::rememberForever('paymentMethods', function () {
            return OrderPaymentMethod::where('is_active',1)->latest()->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }


    public function shippingMethodList(): \Illuminate\Http\JsonResponse
    {
        $data = Cache::rememberForever('shippingMethods', function () {
            return OrderDeliveryMethod::where('is_active',1)->latest()->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }


    public function orderStatusList(): \Illuminate\Http\JsonResponse
    {
        $active = GeneralSetting::first()->delivery_status;

        $data = Cache::remember('orderStatuses', 24*60*60*7, function() use($active) {
            return OrderStatus::when($active == 1, function($q) {
                return $q->whereNot('name', 'Delivered');
            })->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }


    public function getAdditionalChargeList(): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('orderAdditionalCharges', 60*60*24*7, function () {
            return $this->service->getCharges();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function storeCharge(Request $request): \Illuminate\Http\JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'required|unique:order_additional_charges,name|string|max:50',
            'is_percentage'=> 'required|in:0,1',
            'amount'    => ['required','numeric',
                function($attr, $val, $fail) use ($request) {
                    if($request->input('is_percentage') == 1 && $val > 100) {
                        $fail('Amount must not be greater than 100.');
                    }
                }]
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        $this->service->storeOrderCharges($request);

        return response()->json(['status' => true], 201);
    }

    public function updateCharge(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'required|string|max:50|unique:order_additional_charges,name,'.$id,
            'is_percentage'=> 'required|in:0,1',
            'amount'    => ['required','numeric',
                function($attr, $val, $fail) use ($request) {
                    if($request->input('is_percentage') == 1 && $val > 100) {
                        $fail('Amount must not be greater than 100.');
                    }
                }],
            'status'    => 'required|in:0,1'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        $this->service->updateOrderCharge($request, $id);

        return response()->json(['status' => true]);
    }

    public function deleteCharge($id): \Illuminate\Http\JsonResponse
    {
        $this->service->deleteOrderCharge($id);

        return response()->json(['status' => true]);
    }
}
