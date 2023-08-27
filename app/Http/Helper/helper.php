<?php

use App\Mail\OrdersMail;
use App\Models\Admin;
use App\Models\OrderPickupAddress;
use App\Models\PickupAddress;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

function notifyUser($order_number): void
{
    try {
        $to = auth()->user()->username;

        $user_name = auth()->user()->name;

        $message = "Dear {$user_name},\n\nYou have placed a new order. Your order number is {$order_number}. We will notify you shortly when the order is ready for shipment.";

        $mail_data = [
            'title' => 'New Order',
            'body' => $message
        ];

        Mail::to($to)->queue(new OrdersMail($mail_data));
    } catch (\Throwable $th) {

    }
}

function notifyAdmins($order_number): void
{
    try {
        $admin_emails = User::whereHas('roles', function ($query) {
            $query->whereIn('id', [1, 2]);
        })->get();

        $user_name = auth()->user()->name;

        $message = "A new order has been placed by user: {$user_name}. The order number is {$order_number}.";

        foreach ($admin_emails as $email) {
            $to = $email->username;

            $mail_data = [
                'title' => 'New Order',
                'body' => $message
            ];

            Mail::to($to)->queue(new OrdersMail($mail_data));
        }
    } catch (\Throwable $th) {

    }
}

function getDeliveryCharge($address_id, $total_weight, $total_price): float|int
{
    $address = UserAddress::find($address_id);

    $pickup_address = OrderPickupAddress::first();

    if(is_null($address) || is_null($pickup_address)) {
        return 0;
    }

    if ($address->upazila->district->division_id == $pickup_address->upazila->district->division_id) {

        if ($address->upazila->district_id == $pickup_address->upazila->district_id) {
            $delivery_price = 55 + ($total_weight * 25);
        }else{
            $delivery_price = 120 + ($total_weight * 30) + ($total_price * 0.01);
        }

    }else {
        $delivery_price = 120 + ($total_weight * 30) + ($total_price * 0.01);
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

        $status = curl_exec($ch);
        curl_close($ch);
        Log::info($status);
    } catch (Throwable $e)
    {}
}
