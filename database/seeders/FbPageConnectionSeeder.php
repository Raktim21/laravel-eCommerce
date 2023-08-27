<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FbPageConnectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (env('FB_PAGE_NAME')) {
            Http::post('https://chatbotapi.selopian.us/api/v1/create_page_info', [

                'page_api_domain' => env('APP_URL'),
                'page_api_token' => env('API_ACCESS_TOKEN'),
                'page_site_domain' => env('FRONTEND_URL'),
                'page_name' => env('FB_PAGE_NAME'),

            ]);
        }




    }
}
