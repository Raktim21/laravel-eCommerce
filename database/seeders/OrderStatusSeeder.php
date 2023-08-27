<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderStatus::create([
            'name'  => 'Pending',
        ]);

        OrderStatus::create([
            'name'  => 'Confirmed',
        ]);

        OrderStatus::create([
            'name'  => 'Cancelled',
        ]);

        OrderStatus::create([
            'name'  => 'Delivered',
        ]);



    }
}
