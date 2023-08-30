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

// Route::get('/test', function () {
//     // dd('ok');
//     $client = new Client();
//     $response = $client->post(peperfly()['paperFlyUrl'] . '/API-Order-Tracking/', [
//         'headers' => [
//             'paperflykey' => peperfly()['paperFlyKey']
//         ],
//         'auth' => peperfly()['credential'],
//         'json' => ["ReferenceNumber" => 'GLAMDESMITH-ORD-12546332548'],
//     ]);

//     // dd($response);
//     // return $response;

//     $data = json_decode($response->getBody()->getContents(), true);

//     dd($data);
// });

Route::get('/crone-job', [CroneController::class, 'crone'])->name('crone.job');

Route::controller(GoogleFacebookController::class)->group(function () {
    Route::get('redirect-auth', 'redirect');
    Route::get('auth/google/callback', 'handleGoogleCallback');
    Route::get('auth/facebook/callback', 'handleFacebookCallback');
});
