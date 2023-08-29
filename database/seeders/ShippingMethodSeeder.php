<?php

namespace Database\Seeders;

use App\Models\OrderDeliveryMethod;
use App\Models\ShippingMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderDeliveryMethod::create([
            'name'      => 'Home Delivery',
            'is_active' => 1,
        ]);

        OrderDeliveryMethod::create([
            'name'      => 'Instant delivery',
            'is_active' => 1,
        ]);
    }
}
