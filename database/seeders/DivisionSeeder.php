<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Division::truncate();

        $divisions = array(
            array('id' => '1','country_id' => '1','name' => 'Chattagram','local_name' => 'চট্টগ্রাম','url' => 'www.chittagongdiv.gov.bd'),
            array('id' => '2','country_id' => '1','name' => 'Rajshahi','local_name' => 'রাজশাহী','url' => 'www.rajshahidiv.gov.bd'),
            array('id' => '3','country_id' => '1','name' => 'Khulna','local_name' => 'খুলনা','url' => 'www.khulnadiv.gov.bd'),
            array('id' => '4','country_id' => '1','name' => 'Barisal','local_name' => 'বরিশাল','url' => 'www.barisaldiv.gov.bd'),
            array('id' => '5','country_id' => '1','name' => 'Sylhet','local_name' => 'সিলেট','url' => 'www.sylhetdiv.gov.bd'),
            array('id' => '6','country_id' => '1','name' => 'Dhaka','local_name' => 'ঢাকা','url' => 'www.dhakadiv.gov.bd'),
            array('id' => '7','country_id' => '1','name' => 'Rangpur','local_name' => 'রংপুর','url' => 'www.rangpurdiv.gov.bd'),
            array('id' => '8','country_id' => '1','name' => 'Mymensingh','local_name' => 'ময়মনসিংহ','url' => 'www.mymensinghdiv.gov.bd')
        );

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
