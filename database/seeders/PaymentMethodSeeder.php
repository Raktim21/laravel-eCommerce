<?php

namespace Database\Seeders;

use App\Models\OrderPaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderPaymentMethod::create([
            'name'      => 'Cash On Delivery',
            'is_active' => 1,
        ]);

        OrderPaymentMethod::create([
            'name'      => 'Instant Payment',
            'is_active' => 0,
        ]);

        // OrderPaymentMethod::create([
        //     'name'      => 'Card payment',
        //     'is_active' => 0,
        // ]);

        // OrderPaymentMethod::create([
        //     'name'      => 'Online payments',
        //     'is_active' => 0,
        // ]);
    }
}
