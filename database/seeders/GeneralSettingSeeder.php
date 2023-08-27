<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setting = new GeneralSetting();
        $setting->id        = 1;
        $setting->dashboard_language_id = 1;
        $setting->currency_id           = 1;
        $setting->name      = 'Selopia Ecommerce';
        $setting->logo      = '/uploads/images/general-setting/16872415546487.png';
        $setting->dark_logo = '/uploads/images/general-setting/16872415546487.png';
        $setting->favicon   = '/uploads/images/general-setting/16872415543683.png';
        $setting->email     = 'selopia@gmail.com';
        $setting->phone     = '01700000000';
        $setting->address   = 'Dhaka, Bangladesh';
        $setting->facebook  = 'https://www.facebook.com';
        $setting->twitter   = 'https://www.twitter.com';
        $setting->linkedin  = 'https://www.linkedin.com';
        $setting->instagram = 'https://www.instagram.com';
        $setting->youtube   = 'https://www.youtube.com';
        $setting->google    = 'https://www.google.com';
        $setting->pinterest = 'https://www.pinterest.com';
        $setting->about     = 'Selopia Ecommerce is a laravel ecommerce project';
        $setting->save();
    }
}
