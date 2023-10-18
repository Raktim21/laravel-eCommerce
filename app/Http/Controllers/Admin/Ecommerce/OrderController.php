<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Models\OrderPaymentMethod;
use App\Http\Services\AssetService;
use App\Http\Requests\SalesRequest;
use App\Http\Services\OrderService;
use App\Models\OrderDeliveryMethod;
use App\Models\OrderDeliverySystem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\OrderSearchRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\OrderDeliverySystemService;
use App\Http\Requests\DeliveryChargeLookupRequest;
use App\Http\Requests\AdminOrderStatusChangeRequest;

class OrderController extends Controller
{
    protected $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function deliveryChargeLookup()
    {
        $data = Cache::remember('deliveryChargeLookup', 24*60*60*7, function () {
            return $this->service->getDeliveryChargeData();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], is_null($data) ? 204 : 200);
    }

    public function updateDeliveryChargeLookup(DeliveryChargeLookupRequest $request)
    {
        if ((new AssetService())->activeDeliverySystem() != 1)
        {
            return response()->json([
                'status' => false,
                'errors' => ['You are not allowed to update delivery charge when personal delivery service is disabled.']
            ], 403);
        }

        if($this->service->updateChargeLookup($request))
        {
            Cache::delete('deliveryChargeLookup');
            return response()->json(['status' => true]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['Something went wrong.']
        ], 500);
    }

    public function deliverySystemList()
    {
        $data = Cache::remember('deliverySystems', 24*60*60*7, function () {
            return OrderDeliverySystem::orderBy('id')->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    public function updateDeliverySystem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'system_id' => 'required|in:1,2'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $this->service->changeDeliverySystem($request);

        Cache::delete('deliverySystems');
        Cache::delete('deliveryChargeLookup');

        return response()->json(['status' => true]);
    }


    public function paymentMethodList()
    {
        $data = Cache::rememberForever('paymentMethods', function () {
            return OrderPaymentMethod::where('is_active',1)->latest()->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }


    public function shippingMethodList()
    {
        $data = Cache::rememberForever('shippingMethods', function () {
            return OrderDeliveryMethod::where('is_active',1)->latest()->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
    }


    public function orderStatusList()
    {
        $active = (new AssetService())->activeDeliverySystem();

        $data = Cache::remember('orderStatuses', 24*60*60*7, function() use($active) {
            return OrderStatus::when($active != 1, function($q) {
                return $q->whereNot('name', 'Delivered');
            })->get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }


    public function index(OrderSearchRequest $request)
    {
        $order = $this->service->getOrderList(false);

        return response()->json([
            'status' => true,
            'data'    => $order
        ], $order->isEmpty() ? 204 : 200);
    }


    public function adminOrder(OrderSearchRequest $request)
    {
        $order = $this->service->getOrderList(true);

        return response()->json([
            'status'  => true,
            'data'    => $order
        ], $order->isEmpty() ? 204 : 200);
    }


    public function sales(SalesRequest $request)
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
                'errors'    => ['You can not place an order that weighs over 1kg.']
            ], 422);
        } else {
            return response()->json([
                'status'    => true,
                'data'      => array('order_number' => $status)
            ], 201);
        }
    }


    public function getDeliveryCost()
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
        $delivery_charge = (new OrderDeliverySystemService())->getDeliveryCharge((new AssetService())->activeDeliverySystem(),
            request()->input('user_address_id'), request()->input('total_price'));

        return response()->json([
            'status' => true,
            'data'    => array(
                'delivery_charge' => $delivery_charge
            ),
        ]);
    }


    public function detail($id)
    {
        $data = Cache::remember('orderDetail'.$id, 24*60*60*7, function () use ($id) {
            return $this->service->getData($id);
        });

        return response()->json([
                'status' => true,
                'data' => $data
            ], is_null($data) ? 204 : 200);
    }


    public function changeStatus(AdminOrderStatusChangeRequest $request, $id)
    {
        $order = Order::findOrfail($id);

        if($order->order_status_id == $request->status)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Invalid order status']
            ], 400);
        }

        if($order->order_status_id == 3)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Status of cancelled orders cannot be changed.']
            ], 400);
        }

        if($order->delivery_status == 'Picked')
        {
            return response()->json([
                'status' => false,
                'errors' => ['Status of picked orders cannot be changed.']
            ], 400);
        }

        $status = OrderStatus::findOrFail($request->status);

        $delivery_system = (new AssetService())->activeDeliverySystem();

        if($delivery_system != 1 && $request->status == 4)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Order status cannot be changed to delivered when personal delivery system is disabled.']
            ], 400);
        }

        if ($order->order_status_id > $status->id) {
            return response()->json([
                'status' => false,
                'errors' => ['Order status cannot be changed to '. $status->name .' when the current status is '. $order->status->name .'.']
            ], 400);
        }

        if(($request->status == 2 || $request->status == 4) && !$this->service->checkEligibility($order, $request->shop_branch_id))
        {
            return response()->json([
                'status'    => false,
                'errors'    => ['Some of the order items are not available in the selected branch.']
            ], 400);
        }

        if($request->status == 2)
        {
            if ($delivery_system == 2)
            {
//                $weight = $this->service->getOrderWeight($order);
                (new OrderDeliverySystemService())->eCourierOrder($order);
            } else if ($delivery_system == 3)
            {
                (new OrderDeliverySystemService())->pandaGoOrder($order);
            }
        }

        if($request->status == 3 && $order->delivery_system_id != 1)
        {
            if($order->delivery_tracking_number)
            {
                if($order->delivery_system_id == 2)
                {
                    $response = (new OrderDeliverySystemService())->eCourierCancelOrder($order->delivery_tracking_number);
                } else
                {
                    $response = (new OrderDeliverySystemService())->pandaGoCancelOrder($order->delivery_tracking_number);
                }

                if ($response != 'done')
                {
                    return response()->json([
                        'status' => false,
                        'errors' => [$response]
                    ], 400);
                }
            }
            $order->delivery_tracking_number = null;
            $order->delivery_status = 'Cancelled';
        }
        if($request->status == 4)
        {
            $order->delivery_status    = 'Delivered';
            $order->paid_amount        = $order->total_amount;
            $order->payment_status_id  = 2;
        }
        $order->order_status_id = $request->status;

        if(is_null($order->shop_branch_id))
        {
            $order->shop_branch_id  = $request->shop_branch_id;
        }
        $order->order_status_updated_by = auth()->user()->id;
        $order->merchant_remarks = $request->reason == 'DELIVERY_ETA_TOO_LONG' ? 'Order is cancelled because delivery time is too long.' :
            ($request->reason == 'MISTAKE_ERROR' ? 'Order is cancelled because provided information is incorrect.' :
                ($request->reason == 'REASON_UNKNOWN' ? 'Order is cancelled for some unknown reason.' : null));

        $order->save();

        return response()->json([
            'status' => true,
        ]);
    }


    public function changeNote(Request $request, $id)
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


    public function getAdditionalChargeList()
    {
        $data = Cache::remember('orderAdditionalCharges'.request()->get('status',0), 60*60*24*7, function () {
            return $this->service->getCharges();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function storeCharge(Request $request)
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


    public function updateCharge(Request $request, $id)
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


    public function deleteCharge($id)
    {
        $this->service->deleteOrderCharge($id);

        return response()->json(['status' => true]);
    }
}
