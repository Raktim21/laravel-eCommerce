<?php

namespace App\Http\Services;

use App\Models\OrderDeliveryChargeLookup;
use App\Models\OrderPickupAddress;
use App\Models\UserAddress;
use GuzzleHttp\Client;

class OrderDeliverySystemService
{
    public function paperFlyOrder($order, $weight): void
    {
        $client = new Client();
        $pickup = OrderPickupAddress::first();
        if(!is_null($pickup))
        {
            $name = '';
            foreach($order->items as $item){
                $name .= $item->combination->product->category->name.'('.$item->quantity.')';
            }
            $response = $client->post(peperfly()['paperFlyUrl'].'/OrderPlacement', [
                'headers' => [
                    'paperflykey' =>  peperfly()['paperFlyKey']
                ],
                'auth' =>  peperfly()['credential'],
                'json' => [
                    "merOrderRef"          => $order->order_number,
                    "pickMerchantName"     => $pickup->name,
                    "pickMerchantAddress"  => $pickup->address,
                    "pickMerchantThana"    => $pickup->upazila->name,
                    "pickMerchantDistrict" => $pickup->upazila->district->name,
                    "pickupMerchantPhone"  => $pickup->phone,
                    "productSizeWeight"    => "standard",
                    "productBrief"         => $name,
                    "packagePrice"         => $order->total_amount + 2,
                    "max_weight"           => $weight.'kg',
                    "deliveryOption"       => "regular",
                    "custname"             => $order->user->name,
                    "custaddress"          => $order->deliveryAddress->address,
                    "customerThana"        => $order->deliveryAddress->upazila->name,
                    "customerDistrict"     => $order->deliveryAddress->upazila->district->name,
                    "custPhone"            => $order->deliveryAddress->phone_no,
                ],
            ]);

            if ($response->getStatusCode() == 200) {

                $data = json_decode($response->getBody());
                $order->delivery_tracking_number = $data->success->tracking_number;
                $order->save();
            }
        }
    }

    public function pandaGoOrder($order)
    {
        $client = new Client();
        $pickup = OrderPickupAddress::first();

        if($pickup && $pickup->lat && $pickup->lng)
        {
            $response = $client->post(pandago()['pandaGoUrl'].'/orders', [
                'headers' => [
                    'Authorization' =>  'Bearer ' . pandago()['access_token'],
                    'Content-Type'  => 'application/json'
                ],
                'json' => [
                    'sender' => [
                        'name'          => $pickup->name,
                        'phone_number'  => $pickup->phone,
                        'location'      => [
                            'address'   => $pickup->address,
                            'latitude'  => $pickup->lat,
                            'longitude' => $pickup->lng
                        ]
                    ],
                    'recipient' => [
                        'location'  => [
                            'address'   => $order->deliveryAddress->address,
                            'latitude'  => $order->deliveryAddress->lat,
                            'longitude' => $order->deliveryAddress->lng
                        ]
                    ],
                ],
                'amount' => $order->total_amount,
                'payment_method' => 'CASH_ON_DELIVERY'
            ]);

            if ($response->getStatusCode() == 201)
            {
                $data = json_decode($response->getBody());
                $order->delivery_tracking_number = $data->order_id;
                $order->save();
            }
        }
    }

    public function getDeliveryCharge($delivery_system, $delivery_address_id, $total_price)
    {
        if ($delivery_system == 1) // personal
        {
            return $this->getPersonalDeliveryCharge($delivery_address_id, $total_price);
        }
        else if ($delivery_system == 2) // paperfly
        {
            return $this->getPaperFlyDeliveryCharge($delivery_address_id, $total_price);
        }
        else if ($delivery_system == 3) // pandago
        {
            return $this->getPandaGoDeliveryCharge($delivery_address_id, $total_price);
        }

        return 0;
    }

    private function getPersonalDeliveryCharge($address_id, $total_price)
    {
        $address = UserAddress::find($address_id);
        $lookup = OrderDeliveryChargeLookup::orderBy('id')->get();
        $pickup_address = OrderPickupAddress::first();

        if(is_null($address) || is_null($pickup_address)) {
            return 0;
        }

        if ($address->upazila->district->division_id == $pickup_address->upazila->district->division_id) {

            if ($address->upazila->district_id == $pickup_address->upazila->district_id) {
                $delivery_price = $lookup[0]->amount;
            } else {
                $delivery_price = $lookup[1]->amount + (($total_price + $lookup[1]->amount) * 0.01);
            }

        } else {
            $delivery_price = $lookup[2]->amount + (($total_price + $lookup[2]->amount) * 0.01);
        }

        return $delivery_price;
    }

    private function getPaperFlyDeliveryCharge($address_id, $total_price): float|int
    {
        $address = UserAddress::find($address_id);

        $pickup_address = OrderPickupAddress::first();

        if(is_null($address) || is_null($pickup_address)) {
            return 0;
        }

        if ($address->upazila->district->division_id == $pickup_address->upazila->district->division_id) {

            if ($address->upazila->district_id == $pickup_address->upazila->district_id) {
                $delivery_price = 55;
            } else {
                $delivery_price = 90 + (($total_price + 90) * 0.01);
            }

        } else {
            $delivery_price = 120 + (($total_price + 120) * 0.01);
        }

        return $delivery_price;
    }

    private function getPandaGoDeliveryCharge($address_id, $total_price)
    {
        $client = new Client();
        $pickup = OrderPickupAddress::first();
        $address = UserAddress::find($address_id);

        if($pickup && $pickup->lat && $pickup->lng)
        {
            $response = $client->post(pandago()['pandaGoUrl'] . '/orders/fee', [
                'headers' => [
                    'Authorization' => 'Bearer ' . pandago()['access_token'],
                    'Content-Type'  => 'application/json'
                ],
                'json'    => [
                    'sender' => [
                        'name'          => $pickup->name,
                        'phone_number'  => $pickup->phone,
                        'location'      => [
                            'address'   => $pickup->address,
                            'latitude'  => $pickup->lat,
                            'longitude' => $pickup->lng
                        ]
                    ],
                    'recipient' => [
                        'location'  => [
                            'address'   => $address->address,
                            'latitude'  => $address->lat,
                            'longitude' => $address->lng
                        ]
                    ],
                    'amount'            => $total_price,
                    'payment_method'    => 'CASH_ON_DELIVERY',
                    'description'       => ''
                ]
            ]);

            $data = json_decode($response->getBody());

            if ($response->getStatusCode() == 200)
            {
                return $data->estimated_delivery_fee;
            }
        }
        return 0;
    }

    public function cancelOrder($order)
    {
        if($order->delivery_status == 'Picked')
        {
            return 'You cannot cancel order after being picked.';
        }
        if($order->delivery_status == 'Cancelled' || $order->order_status_id == 3)
        {
            return 'Your order has already been cancelled.';
        }
        if($order->delivery_status == 'Delivered' || $order->order_status_id == 4)
        {
            return 'You cannot cancel order after being delivered.';
        }
        if($order->delivery_tracking_number != null)
        {
            if($order->delivery_system_id == 2) {
                $this->paperFlyCancelOrder($order->order_number);
            } else if ($order->delivery_system_id == 3) {
                $response = $this->pandaGoCancelOrder($order->delivery_tracking_number);

                if($response != 'done')
                {
                    return $response;
                }
            }
        }

        $order->delivery_tracking_number = null;
        $order->delivery_status = 'Cancelled';
        $order->order_status_id = 3;
        $order->save();
        return 'done';
    }

    public function paperFlyCancelOrder($order_number)
    {
        $response = (new Client())->post(peperfly()['paperFlyUrl'] . '/api/v1/cancel-order/', [
            'headers' => [
                'paperflykey' =>  peperfly()['paperFlyKey']
            ],
            'auth' => peperfly()['credential'],
            'json' => [
                "order_id" => $order_number,
            ],
        ]);

        json_decode($response->getBody()->getContents(), true);
    }

    public function pandaGoCancelOrder($tracker)
    {
        $response = (new Client())->post(pandago()['pandaGoUrl'] . '/orders/' . $tracker, [
            'headers' => [
                'Authorization' => 'Bearer ' . pandago()['access_token'],
                'Content-Type'  => 'application/json'
            ],
            'json'    => [
                'reason'        => request()->input('reason')
            ]
        ]);

        if ($response->getStatusCode() == 409 || $response->getStatusCode() == 500 || $response->getStatusCode() == 404)
        {
            $data = json_decode($response->getBody());
            return $data->message;
        }

        return 'done';
    }
}
