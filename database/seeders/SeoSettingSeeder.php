<?php

namespace Database\Seeders;

use App\Models\SeoSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeoSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SeoSetting::create([
            'meta_title'       => 'Ecommerce Website',
            'meta_description' => 'Ecommerce Website',
        ]);
    }
}
