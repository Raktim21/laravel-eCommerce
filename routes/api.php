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
use App\Http\Controllers\Admin\Ecommerce\SeoSettingController;
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
use App\Http\Controllers\Admin\POS\BranchController;
use App\Http\Controllers\Customer\Auth\CustomerAuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Customer\Order\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Admin\POS\BillingCartController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Ecommerce\StaticAssetController;
use App\Http\Controllers\Ecommerce\FrontendController;
use App\Http\Controllers\System\GenerateReportController;
use App\Http\Controllers\System\SendMailController;
use App\Http\Controllers\System\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('cron', [SystemController::class, 'runSchedule']);

Route::group(['middleware' => ['ApiAuth']],function () {



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

    Route::controller(CartController::class)->group(function () {

        Route::get('cart-list', 'cartList');
        Route::post('cart-store', 'cartStore');
        Route::put('cart-update/{id}', 'cartUpdate');
        Route::delete('cart-delete/{id}', 'cartDelete');
        Route::post('cart-bulk-delete', 'bulkDelete');
        Route::post('add-cart-from-wishlist', 'addCartFromWishlist');
    });

    Route::controller(FrontendController::class)->group(function () {

        Route::get('general-setting', 'general');
        Route::get('theme', 'theme');
        Route::get('home','home');
        Route::get('category','category');
        Route::get('sub-category-list/{category_id}', 'getSubCategoryList');
        Route::get('brand','brand');
        Route::get('product-details/{id}','productDetails')->middleware('view_count');
        Route::get('product-reviews/{product_id}','productReviews');
        Route::get('product-filter','productFilter');
        Route::get('payment-methods','paymentMethods');
        Route::get('delivery-methods','deliveryMethods');
        Route::get('product/search-suggestion','productSearchSuggestions');
        Route::post('subscribe',  'subscribe');
        Route::post('contact',  'contact');
        Route::post('product-abuse-report', 'reportProduct');
        Route::get('order-additional-charges', 'additionalCharges');
        Route::get('faq-list','faqList');
        Route::get('static-menu','staticMenu');
        Route::get('banners', 'getBanners');
        Route::get('static-menu-content/{id}','staticMenuContent');
        Route::get('flash-sale', 'flashSale');
    });

    Route::get('wish-list', [WishlistController::class, 'getList']);

    Route::get('captcha', [SystemController::class, 'sendCaptcha']);

});


Route::group(['prefix' => 'admin'], function () {

    Route::group(['middleware' => ['ApiStaticAuth']],function () {

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

        Route::get('general-setting', [FrontendController::class, 'general']);

        Route::get('captcha', [SystemController::class, 'sendCaptcha']);

    });

    Route::post('login',[AdminAuthController::class,'login']);
    Route::post('reset-password',[AdminAuthController::class,'resetPassword']);
    Route::post('confirm-password',[AdminAuthController::class,'confirmPassword']);
    Route::get('refresh',[AdminAuthController::class,'refresh']);

    Route::group(['middleware' => ['jwt.verify:admin-api']], function () {

        Route::post('logout',[AdminAuthController::class,'logout']);
        Route::get('me',[AdminAuthController::class,'me']);

        Route::controller(SystemController::class)->group(function () {
            Route::get('clear-cache', 'cache');
            Route::get('change-language', 'changeLanguage');
        });

        Route::controller(ProfileController::class)->group(function () {

            Route::get('auth-permissions', 'permissions');
            Route::put('profile-update','profileUpdate');
            Route::post('avatar-update','avatarUpdate');
            Route::put('password-update','passwordUpdate');
        });

        Route::controller(BranchController::class)->group(function () {

            Route::middleware('permission:create/update/delete branch')->group(function() {
                Route::get('all-branch', 'getAll');
                Route::post('create-branch', 'store');
                Route::put('update-branch/{id}', 'update');
                Route::delete('delete-branch/{id}', 'delete');
            });
        });

        Route::controller(AdminController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete admin']], function() {
                Route::get('admin-list','adminList');
                Route::post('admin-create','adminCreate');
                Route::get('admin-detail/{id}','adminDetail');
                Route::put('admin-update/{id}','adminUpdate');
                Route::post('admin-avatar-update/{id}','adminUpdateAvatar');
                Route::delete('admin-delete/{id}','adminDelete');
                Route::post('admin-bulk-delete', 'bulkDelete');
            });
            //Pick Up Address
            Route::get('pickup-address-list','pickUpAddress');
            Route::put('pickup-address-update','pickUpAddressUpdate')->middleware('permission:update pickup address');
        });

        Route::controller(UserController::class)->group(function () {

            Route::group(['middleware' => ['permission:get user data']], function() {
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
                Route::get('category-list','index');
                Route::post('category-store','store');
                Route::post('category-update/{id}','update');
                Route::delete('category-delete/{id}','destroy');
                Route::post('category-reorder',  'reorder');
                Route::post('category-bulk-delete', 'bulkDelete');
                Route::get('category-status-update/{id}', 'statusUpdate');
            });

        });

        Route::controller(SubCategoryController::class)->group(function () {

            Route::get('sub-category-list/{category_id}', 'getList');

            Route::group(['middleware' => ['permission:create/update/delete product sub-categories']], function() {
                Route::post('sub-category-store','store');
                Route::put('sub-category-update/{id}','update');
                Route::delete('sub-category-delete/{id}','destroy');
                Route::post('sub-category-bulk-delete', 'bulkDelete');
            });
        });

        Route::controller(BrandController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete product brands']], function() {
                Route::get('brand-list','index');
                Route::post('brand-store','store');
                Route::post('brand-update/{id}','update');
                Route::delete('brand-delete/{id}','destroy');
                Route::post('brand-bulk-delete', 'bulkDelete');
            });

        });

        Route::controller(BannerSettingController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete banner setting']], function() {
                Route::get('banner-list','index');
                Route::post('banner-store','store');
                Route::get('banner-detail/{id}','detail');
                Route::post('banner-update/{id}','update');
                Route::delete('banner-delete/{id}','destroy');
                Route::post('banner-bulk-delete', 'bulkDelete');
            });
        });

        Route::controller(SponsorController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete sponsors']], function() {
                Route::get('sponsor-list','index');
                Route::post('sponsor-store','store');
                Route::post('sponsor-update/{id}','update');
                Route::delete('sponsor-delete/{id}','delete');
                Route::post('sponsor-bulk-delete', 'bulkDelete');
            });

        });

        Route::get('subscriber-list',[SubscriberController::class, 'index']);

        Route::controller(SiteBannerController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete banner setting']], function() {
                Route::get('site-banner-list', 'index');
                Route::post('site-banner-update', 'update');
            });
        });

        Route::controller(GeneralSettingController::class)->group(function () {

            Route::get('general-setting-detail','detail');
            Route::post('general-setting-update','update')->middleware('permission:update general setting');

            Route::group(['middleware' => ['permission:create/update/delete faqs']], function() {
                Route::get('faq-list','faqList');
                Route::post('faq-store','faqStore');
                Route::put('faq-update/{id}','faqUpdate');
                Route::delete('faq-delete/{id}','faqDelete');
                Route::post('faq-ordering', 'orderFaq');
            });
        });

        Route::controller(AdminRoleController::class)->group(function () {

            Route::group(['middleware' => ['permission:manage role']], function() {
                Route::get('role-list', 'roleList');
                Route::get('role-detail/{id}', 'roleDetail');
                Route::put('role-update/{id}', 'roleUpdate');
                Route::get('permission-list', 'permissionList');
            });
        });

        Route::controller(ProductController::class)->group(function () {

            Route::group(['middleware' => ['permission:get product data']], function() {
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
                Route::get('product-reviews', 'reviewGetAll');
                Route::get('product-reviews/{id}', 'getReview');
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
                Route::get('flash-sale', 'index');
                Route::post('flash-sale', 'store');
                Route::get('flash-sale/update-status', 'changeStatus');
            });
        });

        Route::controller(InventoryController::class)->group(function () {

            Route::group(['middleware' => ['permission:update/transfer inventory stocks']], function() {
                Route::get('inventory-list', 'getList');
                Route::get('inventory-log', 'getLog');
                Route::put('inventory-update-stock/{id}', 'updateStock');
                Route::put('inventory-update-damage/{id}', 'updateDamage');
                Route::post('inventory-transfer', 'transferStock');
            });

        });

        Route::controller(PromocodeController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update promo-codes']], function() {
                Route::get('promocode-list','index');
                Route::post('promocode-store','store');
                Route::get('promocode-detail/{id}','detail');
                Route::put('promocode-update/{id}', 'update');
                Route::get('promocode-inactive/{id}', 'updateStatus');
            });
        });

        Route::controller(OrderController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete order additional charges']], function() {
                Route::get('order-additional-charges', 'getAdditionalChargeList');
                Route::post('order-additional-charges', 'storeCharge');
                Route::put('order-additional-charges/{id}', 'updateCharge');
                Route::delete('order-additional-charges/{id}', 'deleteCharge');
            });

            Route::group(['middleware' => ['permission:update order delivery charge information']], function () {
                Route::get('order-delivery-charge-lookup-list', 'deliveryChargeLookup');
                Route::put('order-delivery-charge-lookup-update', 'updateDeliveryChargeLookup');
            });

            Route::get('payment-method-list','paymentMethodList');
            Route::get('shipping-method-list','shippingMethodList');
            Route::get('order-status-list','orderStatusList');
            Route::get('order-delivery-system-list', 'deliverySystemList');
            Route::put('update-delivery-system', 'updateDeliverySystem')->middleware('permission:update delivery system');;

            Route::group(['middleware' => ['permission:create/update orders']], function() {
                Route::get('admin-order', 'adminOrder');
                Route::get('order-list','index');
                Route::get('order-detail/{id}','detail');
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

        Route::controller(ContactController::class)->group(function () {

            Route::group(['middleware' => ['permission:manage inbox']], function() {
                Route::get('contact-us-list', 'index');
                Route::delete('contact-us-delete/{id}', 'destroy');
            });

        });

        Route::controller(AdminDashboardController::class)->group(function () {

            Route::get('dashboard','index');
            Route::get('global-data','pending_order_count');
        });

        Route::controller(ThemeSettingController::class)->group(function () {

            Route::get('theme-setting','index');

            Route::group(['middleware' => ['permission:update theme-setting']], function() {
                Route::post('theme-position-update/{id}','positionUpdate');
                Route::post('theme-value-update/{id}','valueUpdate');
                Route::post('theme-active-update/{id}','activeUpdate');
                Route::get('theme-undo','undo');
            });
        });

        Route::controller(ExpenseController::class)->group(function () {

            Route::get('expense-category', 'categoryIndex');
            Route::get('expense', 'expenseIndex');

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

        Route::controller(SalesReportController::class)->group(function () {

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

            Route::get('billing-cart-list', 'cartList');

            Route::group(['middleware' => ['permission:create/update/delete billing']], function() {
                Route::post('billing-cart-store', 'cartStore');
                Route::get('convert-billing-to-order/{id}', 'convertBilling');
            });
        });

        Route::controller(StaticPageController::class)->group(function () {

            Route::group(['middleware' => ['permission:create/update/delete static content']], function() {
                Route::get('static-content', 'staticContent');
                Route::post('static-content-create', 'staticContentStore');
                Route::get('static-content-detail/{id}', 'staticContentDetail');
                Route::post('static-content-update/{id}', 'staticContentUpdate');
                Route::delete('static-content-delete/{id}', 'staticContentDelete');

                Route::get('static-menu', 'staticMenu');
                Route::get('menu-type','staticMenuTypes');
                Route::post('static-menu-create', 'staticMenuStore');
                Route::get('static-menu-detail/{id}', 'staticMenuDetail');
                Route::post('static-menu-update/{id}', 'staticMenuUpdate');
                Route::delete('static-menu-delete/{id}', 'staticMenuDelete');
                Route::post('static-menu-status-change/{id}', 'staticMenuStatusChange');
            });
        });

        Route::controller(SeoSettingController::class)->group(function () {
            Route::group(['middleware' => ['permission:view/update seo setting']], function() {
                Route::get('seo-setting', 'index');
                Route::post('seo-setting-update', 'update');
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
            Route::get('address-list','addressList');
            Route::post('create-new-address','createNewAddress')->middleware('verify.email');
            Route::get('address-detail/{id}','addressDetail');
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
            Route::get('cart-list','cartList');
            Route::post('cart-store','cartStore');
            Route::put('cart-update/{id}','cartUpdate');
            Route::delete('cart-delete/{id}','cartDelete');
            Route::get('delivery-charge','deliveryCharge');
            Route::get('additional-charge', 'getCharge');
            Route::post('cart-bulk-delete', 'bulkDelete');
            Route::post('add-cart-from-wishlist', 'addCartFromWishlist');
        });

        Route::controller(WishlistController::class)->middleware('verify.email')->group(function () {
            Route::get('wish-list', 'getList');
            Route::post('wish-store', 'store');
            Route::get('convert-to-cart/{id}', 'addToCart');
            Route::delete('wish-delete/{id}', 'delete');
            Route::post('wishlist-bulk-delete', 'bulkDelete');
            Route::delete('wish-item-delete/{id}', 'deleteItem');
            Route::post('wish-list-send/{id}','sendWishList');
        });

        Route::controller(CustomerOrderController::class)->middleware('verify.email')->group(function () {
            Route::get('order-list','orderList');
            Route::post('add-promo', 'addPromo');
            Route::post('order','order');
            Route::get('order-detail/{id}','orderDetail');
            Route::post('user-product-review',  'postReview');
            Route::get('cancel-order/{id}', 'cancelOrder');
            Route::get('available-promo-codes', 'getPromos');
        });

        Route::get('order/invoice/{order_id}', [GenerateReportController::class, 'invoicePDF']);

    });

    Route::post('subscribe', [SubscriberController::class, 'create']);

});
