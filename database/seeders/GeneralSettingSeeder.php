<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
//        $setting->merchant_unique_id    = uuid_create('int');
        $setting->dashboard_language_id = 1;
        $setting->currency_id           = 1;
        $setting->name      = 'Selopia Ecommerce';
        $setting->logo      = '/uploads/images/general-setting/16872415546487.png';
        $setting->dark_logo = '/uploads/images/general-setting/16872415546487.png';
        $setting->favicon   = '/uploads/images/general-setting/16872415543683.png';
        $setting->email     = 'selopia@gmail.com';
        $setting->phone     = '01700000000';
        $setting->address   = 'Dhaka, Bangladesh';
        $setting->facebook  = null;
        $setting->twitter   = null;
        $setting->linkedin  = null;
        $setting->instagram = null;
        $setting->youtube   = null;
        $setting->google    = null;
        $setting->pinterest = null;
        $setting->about     = 'Selopia Ecommerce is a laravel ecommerce project';
        $setting->save();
    }
}
