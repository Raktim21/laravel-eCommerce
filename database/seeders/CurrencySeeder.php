<?php

namespace Database\Seeders;

use App\Models\AboutUs;
use App\Models\Currency;
use App\Models\DashboardLanguage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert([
            [
                'id'            => 1,
                'name'          => 'Taka',
                'icon_text'     => 'BDT',
                'icon_image'    => null,
                'created_at'    => now(),
                'updated_at'    => now()
            ],
            [
                'id'            => 2,
                'name'          => 'US Dollar',
                'icon_text'     => 'USD',
                'icon_image'    => null,
                'created_at'    => now(),
                'updated_at'    => now()
            ]
        ]);
    }
}
