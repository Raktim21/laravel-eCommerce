<?php

namespace App\Providers;

use App\Models\BillingCart;
use App\Models\FlashSale;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\PromoUser;
use App\Observers\BillingCartObserver;
use App\Observers\FlashSaleObserver;
use App\Observers\InventoryObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\PromoCodeObserver;
use App\Observers\PromoUserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Product::observe(ProductObserver::class);
        Order::observe(OrderObserver::class);
        BillingCart::observe(BillingCartObserver::class);
        PromoCode::observe(PromoCodeObserver::class);
        PromoUser::observe(PromoUserObserver::class);
        Inventory::observe(InventoryObserver::class);
        FlashSale::observe(FlashSaleObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
