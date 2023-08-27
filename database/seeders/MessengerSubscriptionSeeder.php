<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessengerSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('messenger_subscription_types')->insert([
            ['id' => 1, 'name' => 'Subscribe for Promo Discounts'],
            ['id' => 2, 'name' => 'Subscribe for Tracking Orders'],
        ]);
    }
}
