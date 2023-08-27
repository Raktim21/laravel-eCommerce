<?php

namespace Database\Seeders;

use App\Models\StaticMenuType;
use Dflydev\DotAccessData\Data;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaticMenuTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StaticMenuType::create([
            'name' => 'Header',
        ]);
        StaticMenuType::create([
            'name' => 'Footer',
        ]);
    }
}
