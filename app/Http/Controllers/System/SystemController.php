<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function runSchedule(): void
    {
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
        $pwd = Hash::make('vint13');

        Log::info($pwd);

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
}
