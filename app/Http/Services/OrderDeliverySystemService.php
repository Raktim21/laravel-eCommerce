<?php

namespace App\Http\Services;

use App\Models\OrderPickupAddress;
use App\Models\UserAddress;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

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

    public function pandaGoOrder($order, $weight)
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

    public function getPandagoDeliveryCharge(Request $request)
    {
        $client = new Client();
        $pickup = OrderPickupAddress::first();
        $address = UserAddress::find($request->user_address_id);

        if($pickup && $pickup->lat && $pickup->lng)
        {
            $response = $client->post('https://private-anon-68dbbb42f2-pandago.apiary-mock.com/sg/api/v1/orders/fee', [
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
                    'amount'            => $request->total_price,
                    'payment_method'    => 'CASH_ON_DELIVERY',
                    'description'       => $request->description
                ]
            ]);

            $data = json_decode($response->getBody());

            if ($response->getStatusCode() == 200)
            {
                return $data->estimated_delivery_fee;
            } else {
                return $data->message;
            }
        }
        return 'No geolocation provided for order pickup address.';
    }
}

