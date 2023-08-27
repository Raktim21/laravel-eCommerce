<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CroneController extends Controller
{
    public function crone(){

        Artisan::call('schedule:run');
        // Artisan::call('optimize');
    }
}
