<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MerchantPaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('merchant_payment_methods')->insert([
            ['name' => 'Bank', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EFTN', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bkash', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rocket', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
