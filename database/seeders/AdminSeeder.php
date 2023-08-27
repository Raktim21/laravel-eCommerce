<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'shop_branch_id'    => 1,
            'name'              => 'Admin',
            'username'          => 'admin@admin.com',
            'password'          => Hash::make('admin@123@'),
        ]);

        UserProfile::create([
            'user_id'           => 1,
            'user_sex_id'       => 1,
            'image'             => '/uploads/customer/avatar/16908970508639.png'
        ]);
    }
}
