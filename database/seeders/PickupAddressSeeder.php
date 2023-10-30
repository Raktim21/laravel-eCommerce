<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\OrderPickupAddress;
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
        $address = new OrderPickupAddress();
        $address->pickup_unique_id = rand(10000, 999990);
        $address->name = 'Selopia';
        $address->phone = '01700000000';
        $address->email = 'selopia@selopia.us';
        $address->upazila_id = Country::find(1)->divisions()->first()->districts()->first()->subDistricts()->first()->id;
        $address->union_id = Country::find(1)->divisions()->first()->districts()->first()->subDistricts()->first()->unions()->first()->id ?? null;
        $address->address = 'House 1, Road 1, Block A, Section 1, Mirpur, Dhaka';
        $address->postal_code = '1216';
        $address->lat = '23.6974228';
        $address->lng = '90.5088217';
        $address->save();
    }
}
