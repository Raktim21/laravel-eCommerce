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

function eCourier(): array
{
    return array(
        'user_id'       => 'U6013',

//        // development
//        'url'           => 'https://staging.ecourier.com.bd/api',
//        'api_key'       => '34PK',
//        'api_secret'    => 'PGE5w',

        // production
        'url'           => 'https://backoffice.ecourier.com.bd/api',
        'api_key'       => 'YrWD',
        'api_secret'    => 'RI0UU'
    );
}


function pandago(): array
{
    return array(
        'pandaGoUrl'    => 'https://pandago-api-sandbox.deliveryhero.io/sg/api/v1',
        'clientID'      => 'test-client',
        'keyID'         => 'test-key',
        'access_token'  => 'test-token'
    );
}

function saveImage($image, $path, $model, $field): bool
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

function saveImageFromMedia($image_id, $model, $field): void
{
    $image = GalleryHasImage::find($image_id);
    $model->$field = $image->image_url;
    $model->save();

    $image->increment('usage');
}

function deleteFile($filepath): void
{
    $img = GalleryHasImage::where('image_url', $filepath)->first();

    if (!$img) {
        if (File::exists(public_path($filepath))) {
            File::delete(public_path($filepath));
        }
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
