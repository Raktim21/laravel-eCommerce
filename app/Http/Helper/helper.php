<?php

use App\Models\GalleryHasImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

//function paperfly(): array
//{
//    return array(
//        'paperFlyUrl'   => 'https://api.paperfly.com.bd',
//        'credential'    => ['m142154', '968556'],
//        'paperFlyKey'   => 'Paperfly_~La?Rj73FcLm'
//    );
//}

function eCourier()
{
    return array(
        'url'           => 'https://staging.ecourier.com.bd/api',
        'user_id'       => 'U6013',
        'api_key'       => '34PK',
        'api_secret'    => 'PGE5w'
    );
}


function pandago()
{
    return array(
        'pandaGoUrl'    => 'https://pandago-api-sandbox.deliveryhero.io/sg/api/v1',
        'clientID'      => 'test-client',
        'keyID'         => 'test-key',
        'access_token'  => 'test-token'
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
    $img = GalleryHasImage::where('image_url', $filepath)->first();

    if (!$img && File::exists(public_path($filepath))) {
        File::delete(public_path($filepath));
    }
    else {
        $img->decrement('usage');
    }
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
