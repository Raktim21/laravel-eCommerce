<?php

namespace Database\Seeders;

use App\Models\UserSex;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserSex::create([
            'name' => 'Male'
        ]);

        UserSex::create([
            'name' => 'Female'

        ]);

        UserSex::create([
            'name' => 'Prefer not to say'
        ]);
    }
}
