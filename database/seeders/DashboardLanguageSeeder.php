<?php

namespace Database\Seeders;

use App\Models\AboutUs;
use App\Models\DashboardLanguage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('dashboard_languages')->insert([
            [
                'id'            => 1,
                'name'          => 'English',
                'created_at'    => now(),
                'updated_at'    => now()
            ],
            [
                'id'            => 2,
                'name'          => 'Bangla',
                'created_at'    => now(),
                'updated_at'    => now()

            ]
        ]);
    }
}
