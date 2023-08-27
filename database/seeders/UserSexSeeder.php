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
        // DB::table('user_sexes')->insert([
        //     ['id' => 1, 'name' => 'Male'],
        //     ['id' => 2, 'name' => 'Female'],
        //     ['id' => 3, 'name' => 'Preferred Not To Say']
        // ]);

        UserSex::create([
            'name' => 'Male'
        ]);

        UserSex::create([
            'name' => 'Female'

        ]);

        UserSex::create([
            'name' => 'Preferred Not To Say'
        ]);
    }
}
