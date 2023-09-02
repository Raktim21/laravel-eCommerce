<?php

use App\Http\Controllers\System\CroneController;
use App\Http\Controllers\System\GoogleFacebookController;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/crone-job', [CroneController::class, 'crone'])->name('crone.job');

Route::controller(GoogleFacebookController::class)->group(function () {
    Route::get('redirect-auth', 'redirect');
    Route::get('auth/google/callback', 'handleGoogleCallback');
    Route::get('auth/facebook/callback', 'handleFacebookCallback');
});

Route::get('phpinfo',function (){
    return phpinfo();
});
