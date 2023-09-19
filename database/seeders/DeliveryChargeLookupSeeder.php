<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryChargeLookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('order_delivery_charge_lookups')->insert([
            [
                'category'  => 'Same District',
                'amount'    => 0,
                'created_at'=> now(),
                'updated_at'=> now()
            ],
            [
                'category'  => 'Different District, Same Division',
                'amount'    => 0,
                'created_at'=> now(),
                'updated_at'=> now()
            ],[
                'category'  => 'Different Division',
                'amount'    => 0,
                'created_at'=> now(),
                'updated_at'=> now()
            ]
        ]);
    }
}
