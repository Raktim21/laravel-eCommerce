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
                'detail' => 'Your personal delivery system.',
                'active_status' => 1,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'title' => 'XIT',
                'detail' => 'XIT is a one-stop delivery service provider company offering doorstep delivery services all around Bangladesh,
                            with warehousing and packaging facilities.',
                'active_status' => 0,
                'created_at' => now(), 'updated_at' => now()],
            [
                'title' => 'Pandago',
                'detail' => 'Pandago is an instant package delivery service to send food to your customers across zone borders up to 10KM from your location.',
                'active_status' => 0,
                'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('order_delivery_charge_lookups')->insert([
            [
                'category' => 'Same District',
                'amount' => 0.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category' => 'Different District, Same Division',
                'amount' => 0.00,
                'created_at' => now(), 'updated_at' => now()
            ],[
                'category' => 'Different Division',
                'amount' => 0.00,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}

