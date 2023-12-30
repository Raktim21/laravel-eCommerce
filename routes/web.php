<?php

use App\Http\Controllers\System\SystemController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/generate-sitemap', 'generateSitemap');
    Route::get('/reset-logs', 'clearLogs');
    Route::get('seed-banks', 'seedBanks');
});
