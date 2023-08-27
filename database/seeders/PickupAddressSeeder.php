<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\OrderPickupAddress;
use App\Models\PickupAddress;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PickupAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adderess = new OrderPickupAddress();
        $adderess->name = 'Selopia';
        $adderess->phone = '01700000000';
        $adderess->email = 'selopia@selopia.us';
        $adderess->upazila_id = Country::find(1)->divisions()->first()->districts()->first()->subDistricts()->first()->id;
        $adderess->union_id = Country::find(1)->divisions()->first()->districts()->first()->subDistricts()->first()->unions()->first()->id ?? null;
        $adderess->address = 'House 1, Road 1, Block A, Section 1, Mirpur, Dhaka';
        $adderess->postal_code = '1216';
        $adderess->save();
    }
}
