<?php

namespace Database\Seeders;

use App\Models\ThemeEventType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeEventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ThemeEventType::create([
           'name' => 'Position',
        ]);
        ThemeEventType::create([
           'name' => 'Value',
        ]);
    }
}
