<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDeliverySystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('order_delivery_systems')->insert([
            [
                'title' => 'Personal Delivery System',
                'detail' => '',
                'active_status' => 1,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'title' => 'Paperfly Delivery System',
                'detail' => 'Paperfly is a one-stop logistics solutions provider company offering doorstep delivery services all around Bangladesh at the union level,
                            along with warehousing and fulfillment facilities.',
                'active_status' => 0,
                'created_at' => now(), 'updated_at' => now()],
            [
                'title' => 'Pandago Delivery System',
                'detail' => 'Pandago is an instant package delivery service to send food to your customers across zone borders up to 10KM from your location.',
                'active_status' => 0,
                'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

