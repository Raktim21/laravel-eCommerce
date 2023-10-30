<?php

use App\Http\Controllers\System\SystemController;
use App\Http\Controllers\System\GoogleFacebookController;
use Illuminate\Support\Facades\Http;
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

Route::controller(SystemController::class)->group(function () {
    Route::get('email-configuration', 'configureEmailView')->name('configure-email-view');
    Route::get('save-email-configuration', 'configureEmail')->name('configureEmail');
    Route::get('/generate-sitemap', 'generateSitemap');
    Route::get('/reset-logs', 'clearLogs');
});

Route::controller(GoogleFacebookController::class)->group(function () {
    Route::get('redirect-auth', 'redirect');
    Route::get('auth/google/callback', 'handleGoogleCallback');
    Route::get('auth/facebook/callback', 'handleFacebookCallback');
});
