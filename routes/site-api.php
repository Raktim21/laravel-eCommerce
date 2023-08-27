<?php

use App\Http\Controllers\Ecommerce\StaticAssetController;
use App\Http\Controllers\GenerateReportController;
use App\Http\Controllers\System\MessengerController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Analytics\SalesReportController;

Route::group(['middleware' => ['ApiAuth']], function() {

    Route::controller(StaticAssetController::class)->group(function () {

        Route::get('country-list','countryList');
        Route::get('division-list','divisionList');
        Route::get('district-list','districtList');
        Route::get('sub-district-list','subDistrictList');
        Route::get('union-list','unionList');
    });

    Route::controller(MessengerController::class)->group(function () {
        Route::get('product-list', 'productFilter');
        Route::post('cancel_order', 'cancelOrder');
        Route::post('subscribe', 'subscribe');
        Route::post('shop_review', 'storeReview');
        Route::post('track_order', 'getOrderStatus');
        Route::post('chat_order', 'order');
    });
});

Route::get('order/invoice', [MessengerController::class, 'invoicePDF']);
