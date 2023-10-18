<?php

namespace App\Http\Controllers\System;

use App\Models\Districts;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function runSchedule(): void
    {
//        Artisan::call('queue:work');
        Artisan::call('schedule:run');
    }

    public function configureEmailView()
    {
        return view('email_configuration');
    }

    public function configureEmail(Request $request)
    {
        putenv("hey=me");
//        putenv("MAIL_MAILER=".$request->mailer);
//        putenv("MAIL_HOST=".$request->host);
//        putenv("MAIL_PORT=".$request->port);
//        putenv(['MAIL_USERNAME' => $request->username]);
//        putenv(['MAIL_PASSWORD' => $request->password]);
//        putenv(['MAIL_ENCRYPTION' => $request->encryption]);
//        putenv(['MAIL_FROM_ADDRESS' => $request->email]);
//        putenv(['MAIL_FROM_NAME' => $request->name]);
    }

    public function sendCaptcha()
    {
        return response()->json([
            'status'  => true,
            'captcha' => Captcha::create('default',true)
        ]);
    }

    public function cache()
    {
        Artisan::call('cache:clear');

        return response()->json([
            'status' => true,
        ]);
    }

    public function changeLanguage()
    {
        App::setLocale(request()->lang);

        session()->put('locale', request()->lang);

        return response()->json([
            'status' => true,
        ]);
    }

    public function seeder()
    {
        Artisan::call('db:seed --class=UpdateThemeSeeder');
    }

    public function locationChecker()
    {
        $client = new Client();

        $response1 = $client->post('https://staging.ecourier.com.bd/api/city-list', [
            'headers' => [
                'Content-Type' => 'application/json',
                'API-KEY'      => '34PK',
                'API-SECRET'   => 'PGE5w',
                'USER-ID'      => 'U6013'
            ],
            'json' => [

            ]
        ]);

        if ($response1->getStatusCode() == 200)
        {
            $data = json_decode($response1->getBody(), true);

            $db_data = Districts::orderBy('id')->get();

            $db_data = json_decode($db_data, true);


        }
    }
}
