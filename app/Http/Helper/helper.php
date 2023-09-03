<?php

use App\Models\OrderPickupAddress;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

function peperfly(): array
{
    return array(
        'paperFlyUrl'   => 'https://api.paperfly.com.bd',
        'credential'    => ['m142154', '968556'],
        'paperFlyKey'   => 'Paperfly_~La?Rj73FcLm'
    );
}


function saveImage($image, $path, $model, $field)
{
    try {
        $image_name = time() . rand(100, 9999) . '.' . $image->getClientOriginalExtension();
        $image->move(public_path($path), $image_name);
        $model->$field = $path . $image_name;
        $model->save();

        return true;

    } catch (\Throwable $th) {
        return false;
    }
}

function deleteFile($filepath): void
{
    if (File::exists(public_path($filepath)))
    {
        File::delete(public_path($filepath));
    }
}

function getDeliveryCharge($address_id, $total_price): float|int
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

function sendMessengerResponse($response, $route): void
{
    $url = 'https://chatbotapi.selopian.us/api/v1/' . $route;

    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_exec($ch);
        curl_close($ch);
    } catch (Throwable $e)
    {}
}

function forgetCaches($prefix): void
{
    for ($i=1; $i < 1000; $i++) {
        $key = $prefix . $i;
        if (Cache::has($key)) {
            Cache::forget($key);
        } else {
            break;
        }
    }
}
