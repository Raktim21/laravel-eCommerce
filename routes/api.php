<?php

require __DIR__. '/site-api.php';

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\Analytics\AdminDashboardController;
use App\Http\Controllers\Admin\Analytics\SalesReportController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Ecommerce\FlashSaleController;
use App\Http\Controllers\Admin\Ecommerce\ProductAttributeController;
use App\Http\Controllers\Admin\Ecommerce\BannerSettingController;
use App\Http\Controllers\Admin\Ecommerce\BrandController;
use App\Http\Controllers\Admin\Ecommerce\CategoryController;
use App\Http\Controllers\Admin\Ecommerce\ContactController;
use App\Http\Controllers\Admin\Ecommerce\GeneralSettingController;
use App\Http\Controllers\Admin\Ecommerce\OrderController;
use App\Http\Controllers\Admin\Ecommerce\ProductController;
use App\Http\Controllers\Admin\Ecommerce\PromocodeController;
use App\Http\Controllers\Admin\Ecommerce\SiteBannerController;
use App\Http\Controllers\Admin\Ecommerce\SponsorController;
use App\Http\Controllers\Admin\Ecommerce\SubCategoryController;
use App\Http\Controllers\Admin\Ecommerce\SubscriberController;
use App\Http\Controllers\Admin\Ecommerce\ThemeSettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\POS\ExpenseController;
use App\Http\Controllers\Admin\POS\InventoryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Customer\Auth\CustomerAuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Customer\Order\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Admin\POS\BillingCartController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Ecommerce\StaticAssetController;
use App\Http\Controllers\Ecommerce\FrontendController;
use App\Http\Controllers\GenerateReportController;
use App\Http\Controllers\SendMailController;
use App\Http\Controllers\System\CroneController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Mews\Captcha\Facades\Captcha;


Route::group(['middleware' => ['ApiAuth']],function () {


    Route::prefix('asset')->middleware('gzip')->controller(StaticAssetController::class)->group(function () {

        Route::get('country-list','countryList');
        Route::get('division-list','divisionList');
        Route::get('district-list','districtList');
        Route::get('sub-district-list','subDistrictList');
        Route::get('union-list','unionList');
        Route::get('language-list', 'languageList');
        Route::get('currency-list', 'currencyList');
        Route::get('gender-list', 'genderList');
    });

    Route::controller(CartController::class)->group(function () {

        Route::get('cart-list', 'cartList')->middleware('gzip');
        Route::post('cart-store', 'cartStore');
        Route::put('cart-update/{id}', 'cartUpdate');
        Route::delete('cart-delete/{id}', 'cartDelete');
        Route::post('cart-bulk-delete', 'bulkDelete');
        Route::put('add-cart-from-wishlist', 'addCartFromWishlist');
    });

    Route::controller(FrontendController::class)->group(function () {

        Route::get('general-setting', 'general')->middleware('gzip');
        Route::get('theme', 'theme')->middleware('gzip');
        Route::get('home','home')->middleware('gzip');
        Route::get('category','category')->middleware('gzip');
        Route::get('sub-category-list/{category_id}', 'getSubCategoryList')->middleware('gzip');
        Route::get('brand','brand')->middleware('gzip');
        Route::get('product-details/{id}','productDetails')->middleware('view_count');
        Route::get('product-reviews/{product_id}','productReviews')->middleware('gzip');
        Route::get('product-filter','productFilter')->middleware('gzip');
        Route::get('payment-methods','paymentMethods')->middleware('gzip');
        Route::get('delivery-methods','deliveryMethods')->middleware('gzip');
        Route::get('product/search-suggestion','productSearchSuggestions')->middleware('gzip');
        Route::post('subscribe',  'subscribe');
        Route::post('contact',  'contact');
        Route::post('product-abuse-report', 'reportProduct');
        Route::get('order-additional-charges', 'additionalCharges')->middleware('gzip');
        Route::get('faq-list','faqList')->middleware('gzip');
        Route::get('static-menu','staticMenu')->middleware('gzip');
        Route::get('banners', 'getBanners')->middleware('gzip');
        Route::get('static-menu-content/{id}','staticMenuContent')->middleware('gzip');
        Route::get('flash-sale', 'flashSale')->middleware('gzip');
    });

    Route::get('/captcha', function () {

        return response()->json([
            'status'  => true,
            'captcha' => Captcha::create('default',true)
        ]);
    });

});


Route::group(['prefix' => 'admin'], function () {

    Route::group(['middleware' => ['ApiStaticAuth','gzip']],function () {

        Route::prefix('asset')->controller(StaticAssetController::class)->group(function () {

            Route::get('country-list','countryList');
            Route::get('division-list','divisionList');
            Route::get('district-list','districtList');
            Route::get('sub-district-list','subDistrictList');
            Route::get('union-list','unionList');
            Route::get('language-list', 'languageList');
            Route::get('currency-list', 'currencyList');
            Route::get('gender-list', 'genderList');
        });

        Route::get('general-setting', [FrontendController::class, 'general'])->middleware('gzip');

        Route::get('/captcha', function () {
            return response()->json([
                'status'  => true,
                'captcha' => Captcha::create('default',true)
            ]);
        });

    });

    Route::post('login',[AdminAuthController::class,'login']);
    Route::post('reset-password',[AdminAuthController::class,'resetPassword']);
    Route::post('confirm-password',[AdminAuthController::class,'confirmPassword']);
    Route::get('refresh',[AdminAuthController::class,'refresh']);

    Route::group(['middleware' => ['jwt.verify:admin-api']], function () {

        Route::post('logout',[AdminAuthController::class,'logout']);
        Route::get('me',[AdminAuthController::class,'me']);

        //Clear Cache
        Route::get('clear-cache',function(){
            Artisan::call('cache:clear');
            return "Cache is cleared";
        });

        Route::get('change-language',function(){

            App::setLocale(request()->lang);
            session()->put('locale', request()->lang);
            return response()->json([
                'status' => true,
            ]);

        });

        Route::controller(ProfileController::class)->group(function () {

            Route::get('auth-permissions', 'permissions')->middleware('gzip');
            Route::put('profile-update','profileUpdate');
            Route::post('avatar-update','avatarUpdate');
            Route::put('password-update','passwordUpdate');
        });

        Route::controller(BranchController::class)->group(function () {

            Route::middleware('permission:create/update/delete branch')->group(function() {
                Route::get('all-branch', 'getAll')->middleware('gzip');
                Route::post('create-branch', 'store');
                Route::put('update-branch/{id}', 'update');
                Route::delete('delete-branch/{id}', 'delete');
            });
        });

        Route::controller(AdminController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete admin']], function() {
                Route::get('admin-list','adminList')->middleware('gzip');
                Route::post('admin-create','adminCreate');
                Route::get('admin-detail/{id}','adminDetail')->middleware('gzip');
                Route::put('admin-update/{id}','adminUpdate');
                Route::post('admin-avatar-update/{id}','adminUpdateAvatar');
                Route::delete('admin-delete/{id}','adminDelete');
                Route::post('admin-bulk-delete', 'bulkDelete');
            });
            //Pick Up Address
            Route::get('pickup-address-list','pickUpAddress')->middleware('gzip');
            Route::put('pickup-address-update','pickUpAddressUpdate')->middleware('permission:update pickup address');
        });

        Route::controller(UserController::class)->group(function () {

            Route::group(['middleware' => ['permission:get user data','gzip']], function() {
                Route::get('user-list','userList');
                Route::get('user-detail/{id}','userDetail');
                Route::get('user-order/{id}','userOrder');
                Route::get('user-address-list/{id}','userAddressList');
                Route::get('user-order-report-detail/{id}', 'userOrderReport');
            });

            Route::group(['middleware' => ['permission:create/update/delete user']], function() {
                Route::post('user-create','userCreate');
                Route::put('user-update/{id}','userUpdate');
                Route::post('user-avatar-update/{id}','userAvatarUpdate');
                Route::delete('user-delete/{id}','userDelete');
                Route::post('user-bulk-delete', 'bulkDelete');
            });

            Route::group(['middleware' => ['permission:create/update/delete user addresses']], function() {
                Route::post('user-address-create/{id}','userAddressCreate');
                Route::put('user-address-update/{id}','userAddressUpdate');
                Route::delete('user-address-delete/{id}','userAddressDelete');
                Route::put('address-default/{id}','makeDefaultAddress');
                Route::post('user-address-bulk-delete', 'addressBulkDelete');
            });

        });

        Route::controller(CategoryController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete product categories']], function() {
                Route::get('category-list','index')->middleware('gzip');
                Route::post('category-store','store');
                Route::post('category-update/{id}','update');
                Route::delete('category-delete/{id}','destroy');
                Route::post('category-reorder',  'reorder');
                Route::post('category-bulk-delete', 'bulkDelete');
                Route::get('category-status-update/{id}', 'statusUpdate');
            });

        });

        Route::controller(SubCategoryController::class)->group(function () {

            Route::get('sub-category-list/{category_id}', 'getList')->middleware('gzip');

            Route::group(['middleware' => ['permission:create/update/delete product sub-categories']], function() {
                Route::post('sub-category-store','store');
                Route::put('sub-category-update/{id}','update');
                Route::delete('sub-category-delete/{id}','destroy');
                Route::post('sub-category-bulk-delete', 'bulkDelete');
            });
        });

        Route::controller(BrandController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete product brands']], function() {
                Route::get('brand-list','index')->middleware('gzip');
                Route::post('brand-store','store');
                Route::post('brand-update/{id}','update');
                Route::delete('brand-delete/{id}','destroy');
                Route::post('brand-bulk-delete', 'bulkDelete');
            });

        });

        Route::controller(BannerSettingController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete banner setting']], function() {
                Route::get('banner-list','index')->middleware('gzip');
                Route::post('banner-store','store');
                Route::get('banner-detail/{id}','detail')->middleware('gzip');
                Route::post('banner-update/{id}','update');
                Route::delete('banner-delete/{id}','destroy');
                Route::post('banner-bulk-delete', 'bulkDelete');
            });
        });

        Route::controller(SponsorController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete sponsors']], function() {
                Route::get('sponsor-list','index')->middleware('gzip');
                Route::post('sponsor-store','store');
                Route::post('sponsor-update/{id}','update');
                Route::delete('sponsor-delete/{id}','delete');
                Route::post('sponsor-bulk-delete', 'bulkDelete');
            });

        });

        Route::get('subscriber-list',[SubscriberController::class, 'index'])->middleware('gzip');
        Route::get('contact-us-list', [ContactController::class, 'index'])->middleware('gzip');

        Route::controller(SiteBannerController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete banner setting']], function() {
                Route::get('site-banner-list', 'index')->middleware('gzip');
                Route::post('site-banner-update', 'update');
            });
        });

        Route::controller(GeneralSettingController::class)->group(function () {

            Route::get('general-setting-detail','detail')->middleware('gzip');
            Route::post('general-setting-update','update')->middleware('permission:update general setting');

            Route::group(['middleware' => ['permission:create/update/delete faqs']], function() {
                Route::get('faq-list','faqList')->middleware('gzip');
                Route::post('faq-store','faqStore');
                Route::put('faq-update/{id}','faqUpdate');
                Route::delete('faq-delete/{id}','faqDelete');
                Route::post('faq-ordering', 'orderFaq');
            });

            Route::get('delivery-status','deliveryStatus');
            Route::post('delivery-status-update','deliveryStatusUpdate')->middleware('permission:update delivery status');
        });

        Route::controller(AdminRoleController::class)->group(function () {

            Route::group(['middleware' => ['permission:manage role']], function() {
                Route::get('role-list', 'roleList')->middleware('gzip');
                Route::get('role-detail/{id}', 'roleDetail')->middleware('gzip');
                Route::put('role-update/{id}', 'roleUpdate');
                Route::get('permission-list', 'permissionList')->middleware('gzip');
            });
        });

        Route::controller(ProductController::class)->group(function () {

            Route::group(['middleware' => ['permission:get product data','gzip']], function() {
                Route::get('product-list','index');
                Route::get('product-detail/{id}','detail');
                Route::get('product-abuse-reports', 'abuseReports');
                Route::get('product-restock-requests', 'restockRequests');
            });

            Route::group(['middleware' => ['permission:create/update/delete products']], function() {
                Route::post('product-store','store');
                Route::post('product-update/{id}','update');
                Route::delete('product-delete/{id}','destroy');
                Route::post('product-bulk-delete', 'productBulkDelete');
                Route::delete('product-image-delete/{id}','multipleImageDelete');

                Route::get('product-review/change-status/{id}','reviewApproved');
                Route::get('product-reviews', 'reviewGetAll')->middleware('gzip');
                Route::get('product-reviews/{id}', 'getReview')->middleware('gzip');
                Route::put('product-review-reply/{id}', 'reviewReply');
                Route::put('product-abuse-reports/{id}', 'changeAbuseStatus');
            });

        });

        Route::controller(ProductAttributeController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete products']], function() {
                Route::post('attribute-store', 'store');
                Route::post('attribute-variant-store/{attribute_id}', 'storeVariant');
                Route::put('attribute-update/{id}', 'update');
                Route::put('update-attribute-variant/{id}', 'updateVariant');
                Route::post('attribute-delete', 'destroy');
                Route::delete('attribute-value-delete/{id}', 'valueDelete');
                Route::put('product-combination/{id}', 'updateCombination');
                Route::delete('deactivate-product-combination/{id}', 'inactiveCombination');
                Route::get('activate-product-combination/{id}', 'activateCombination');
            });
        });

        Route::controller(FlashSaleController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update flash sale']], function () {
                Route::get('flash-sale', 'index')->middleware('gzip');
                Route::post('flash-sale', 'store');
                Route::get('flash-sale/update-status', 'changeStatus');
            });
        });

        Route::controller(InventoryController::class)->group(function () {

            Route::group(['middleware' => ['permission:update/transfer inventory stocks']], function() {
                Route::get('inventory-list', 'getList')->middleware('gzip');
                Route::get('inventory-log', 'getLog')->middleware('gzip');
                Route::put('inventory-update-stock/{id}', 'updateStock');
                Route::put('inventory-update-damage/{id}', 'updateDamage');
                Route::post('inventory-transfer', 'transferStock');
            });

        });

        Route::controller(PromocodeController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update promo-codes']], function() {
                Route::get('promocode-list','index')->middleware('gzip');
                Route::post('promocode-store','store');
                Route::get('promocode-detail/{id}','detail')->middleware('gzip');
                Route::put('promocode-update/{id}', 'update');
                Route::get('promocode-inactive/{id}', 'updateStatus');
            });
        });

        Route::controller(OrderController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete order additional charges']], function() {
                Route::get('order-additional-charges', 'getAdditionalChargeList')->middleware('gzip');
                Route::post('order-additional-charges', 'storeCharge');
                Route::put('order-additional-charges/{id}', 'updateCharge');
                Route::delete('order-additional-charges/{id}', 'deleteCharge');
            });

            Route::get('payment-method-list','paymentMethodList')->middleware('gzip');
            Route::get('shipping-method-list','shippingMethodList')->middleware('gzip');
            Route::get('order-status-list','orderStatusList')->middleware('gzip');

            Route::group(['middleware' => ['permission:create/update orders']], function() {
                Route::get('admin-order', 'adminOrder')->middleware('gzip');
                Route::get('order-list','index')->middleware('gzip');
                Route::get('order-detail/{id}','detail')->middleware('gzip');
                Route::put('order-status-change/{id}','changeStatus');
                Route::put('admin-note-update/{id}','changeNote');
                Route::post('admin-order', 'sales');
                Route::get('delivery-charge', 'getDeliveryCost');
            });
        });

        Route::controller(GenerateReportController::class)->group(function () {

            Route::get('order/invoice/{order_id}', 'invoicePDF');
            Route::get('billing/invoice/{billing_id}', 'billingPDF');
            Route::get('expense/export/all-data', 'exportExpense');
            Route::get('order/export/all-data', 'exportOrder');
            Route::get('inventory/export/all-data', 'exportInventory');
            Route::post('expense/import/all-data', 'importExpense');

        });

        Route::controller(SendMailController::class)->group(function () {

            Route::group(['middleware' => ['permission:manage inbox']], function() {
                Route::get('order/send_invoice/{order_id}', 'sendInvoice');
                Route::post('contact/send-reply/{id}', 'sendReply');
            });

        });

        Route::controller(AdminDashboardController::class)->group(function () {

            Route::get('dashboard','index')->middleware('gzip');
            Route::get('global-data','global_data');
        });

        Route::controller(ThemeSettingController::class)->group(function () {

            Route::get('theme-setting','index')->middleware('gzip');

            Route::group(['middleware' => ['permission:update theme-setting']], function() {
                Route::post('theme-position-update/{id}','positionUpdate');
                Route::post('theme-value-update/{id}','valueUpdate');
                Route::post('theme-active-update/{id}','activeUpdate');
                Route::get('theme-undo','undo');
            });
        });

        Route::controller(ExpenseController::class)->group(function () {

            Route::get('expense-category', 'categoryIndex')->middleware('gzip');
            Route::get('expense', 'expenseIndex')->middleware('gzip');

            Route::group(['middleware' => ['permission:create/update/delete expense categories']], function() {
                Route::post('expense-category-store', 'categoryStore');
                Route::post('expense-category-update/{id}', 'categoryUpdate');
                Route::delete('expense-category-delete/{id}', 'categoryDelete');
                Route::post('expense-category-bulk-delete', 'categoryBulkDelete');
            });

            Route::group(['middleware' => ['permission:create/update/delete expenses']], function() {
                Route::post('expense-store', 'expenseStore');
                Route::post('expense-update/{id}', 'expenseUpdate');
                Route::delete('expense-delete/{id}', 'expenseDelete');
                Route::post('expense-bulk-delete', 'expenseBulkDelete');
            });

        });

        Route::controller(SalesReportController::class)->middleware('gzip')->group(function () {

            Route::get('kpi_report', 'generalReport');
            Route::get('users/monthly_new_user', 'newUsers');
            Route::get('products/most_viewed', 'mostViewedProducts');
            Route::get('products/most_sold', 'mostSoldProducts');
            Route::get('users/most_purchased', 'mostPurchasedUsers');
            //Route::get('products/individual_report/{product_id}', 'productReport');
            Route::get('yearly_sales_data', 'salesData');
            Route::get('products/category/total_orders', 'mostOrderedCategories');
        });

        Route::controller(NotificationController::class)->group(function () {
            Route::get('notification-list','index');
            Route::get('notification-get','getNotifications');
            Route::get('notification-read/{id}','readNotification');
            Route::post('notification-bulk-read','BulkRead');
        });

        Route::controller(BillingCartController::class)->group(function () {

            Route::get('billing-cart-list', 'cartList')->middleware('gzip');

            Route::group(['middleware' => ['permission:create/update/delete billing']], function() {
                Route::post('billing-cart-store', 'cartStore');
                Route::delete('billing-cart-delete/{id}', 'cartDelete');
                Route::get('convert-billing-to-order/{id}', 'convertBilling');
            });
        });

        Route::controller(StaticPageController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete static content']], function() {
                Route::get('static-content', 'staticContent')->middleware('gzip');
                Route::post('static-content-create', 'staticContentStore');
                Route::get('static-content-detail/{id}', 'staticContentDetail')->middleware('gzip');
                Route::post('static-content-update/{id}', 'staticContentUpdate');
                Route::delete('static-content-delete/{id}', 'staticContentDelete');

                Route::get('static-menu', 'staticMenu')->middleware('gzip');
                Route::get('menu-type','staticMenuTypes')->middleware('gzip');
                Route::post('static-menu-create', 'staticMenuStore');
                Route::get('static-menu-detail/{id}', 'staticMenuDetail')->middleware('gzip');
                Route::post('static-menu-update/{id}', 'staticMenuUpdate');
                Route::delete('static-menu-delete/{id}', 'staticMenuDelete');
                Route::post('static-menu-status-change/{id}', 'staticMenuStatusChange');
            });
        });

    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('notification-list','index');
    });
});


Route::group(['prefix' => 'user'], function () {

    Route::controller(CustomerAuthController::class)->group(function () {

        Route::post('login', 'login');
        Route::post('register', 'register');
        Route::get('refresh', 'refresh');
        Route::post('reset-password', 'resetPassword');
        Route::post('confirm-password', 'confirmPassword');
    });

    Route::group(['middleware' => ['jwt.verify:user-api']], function () {

        Route::controller(CustomerAuthController::class)->group(function () {

            Route::post('verify-email', 'emailVerification');
            Route::get('send-verification-code', 'sendVerificationCode');
            Route::post('logout', 'logout');
            Route::get('me', 'me');
            Route::delete('delete-account', 'deleteAccount');
        });


        Route::controller(CustomerProfileController::class)->group(function () {

            Route::put('profile-update','profileUpdate');
            Route::put('password-update','passwordUpdate');
            Route::post('avatar-update','avatarUpdate');
            Route::get('address-list','addressList')->middleware('gzip');
            Route::post('create-new-address','createNewAddress')->middleware('verify.email');
            Route::get('address-detail/{id}','addressDetail')->middleware('gzip');
            Route::put('address-update/{id}','updateAddress');
            Route::delete('address-delete/{id}','deleteAddress');
            Route::put('address-default/{id}','makeDefaultAddress');
        });

        Route::controller(FrontendController::class)->group(function() {
            Route::post('product-request-restock', 'restockRequest')->middleware('verify.email');
            Route::post('product-abuse-report', 'reportProduct');
        });


        Route::controller(CartController::class)->group(function () {

            Route::put('add-cart-to-user', 'addUserCart'); // after log in
            Route::get('cart-list','cartList')->middleware('gzip');
            Route::post('cart-store','cartStore');
            Route::put('cart-update/{id}','cartUpdate');
            Route::delete('cart-delete/{id}','cartDelete');
            Route::get('delivery-charge/{id}','deliveryCharge');
            Route::get('additional-charge', 'getCharge')->middleware('gzip');
            Route::post('cart-bulk-delete', 'bulkDelete');
            Route::put('add-cart-from-wishlist', 'addCartFromWishlist');
        });

        Route::controller(WishlistController::class)->middleware('verify.email')->group(function () {
            Route::get('wish-list', 'getList')->middleware('gzip');
            Route::post('wish-store', 'store');
            Route::get('convert-to-cart/{id}', 'addToCart');
            Route::delete('wish-delete/{id}', 'delete');
            Route::post('wishlist-bulk-delete', 'bulkDelete');
            Route::delete('wish-item-delete/{id}', 'deleteItem');
            Route::post('wish-list-send/{id}','sendWishList');
        });

        Route::controller(CustomerOrderController::class)->middleware('verify.email')->group(function () {
            Route::get('order-list','orderList')->middleware('gzip');
            Route::post('add-promo', 'addPromo');
            Route::post('order','order');
            Route::get('order-detail/{id}','orderDetail')->middleware('gzip');
            Route::post('user-product-review',  'postReview');
            Route::get('cancel-order/{id}', 'cancelOrder');
        });

        Route::get('order/invoice/{order_id}', [GenerateReportController::class, 'invoicePDF']);

    });

    Route::post('subscribe', [SubscriberController::class, 'create']);

});


Route::get('demo-cron', [CroneController::class, 'cron']);
