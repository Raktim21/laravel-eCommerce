<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function runSchedule(): void
    {
        Artisan::call('schedule:run');
    }

    public function sendCaptcha(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'  => true,
            'captcha' => Captcha::create('default',true)
        ]);
    }

    public function cache(): \Illuminate\Http\JsonResponse
    {
        Artisan::call('cache:clear');

        return response()->json([
            'status' => true,
        ]);
    }

    public function changeLanguage(): \Illuminate\Http\JsonResponse
    {
        App::setLocale(request()->lang);

        session()->put('locale', request()->lang);

        return response()->json([
            'status' => true,
        ]);
    }
}
