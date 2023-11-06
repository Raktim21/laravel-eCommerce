<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CurrencySeeder::class,
            DashboardLanguageSeeder::class,
            MessengerSubscriptionSeeder::class,
            GeneralSettingSeeder::class,
            CountrySeeder::class,
            DivisionSeeder::class,
            DistrictsSeeder::class,
            SubDistrictSeeder::class,
            UnionSeeder::class,
            BranchSeeder::class,
            UserSexSeeder::class,
            AdminSeeder::class,
            RolePermissionSeeder::class,
            OrderStatusSeeder::class,
            OrderDeliverySystemSeeder::class,
            DeliveryChargeLookupSeeder::class,
            PaymentStatusSeeder::class,
            PaymentMethodSeeder::class,
            MerchantPaymentMethodSeeder::class,
            ShippingMethodSeeder::class,
            PickupAddressSeeder::class,
            ThemeCustomizationSeeder::class,
            ThemeEventTypeSeeder::class,
            StaticMenuTypeSeeder::class,
            FbPageConnectionSeeder::class,
            SeoSettingSeeder::class,
        ]);

    }
}
