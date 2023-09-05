<?php

use App\Http\Controllers\System\SystemController;
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

Route::get('/status', function () {
    $client = new Client();
    $response = $client->post(peperfly()['paperFlyUrl'] . '/API-Order-Tracking/', [
        'headers' => [
            'paperflykey' => peperfly()['paperFlyKey']
        ],
        'auth' => peperfly()['credential'],
        'json' => ["ReferenceNumber" => '280823-81901-A11-J6'],
    ]);

    $data = json_decode($response->getBody()->getContents(), true);

    dd($data);
});

Route::get('/crone-job', [SystemController::class, 'crone'])->name('crone.job');

Route::controller(GoogleFacebookController::class)->group(function () {
    Route::get('redirect-auth', 'redirect');
    Route::get('auth/google/callback', 'handleGoogleCallback');
    Route::get('auth/facebook/callback', 'handleFacebookCallback');
});
