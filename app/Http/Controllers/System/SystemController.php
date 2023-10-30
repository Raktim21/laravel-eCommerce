<?php

namespace App\Http\Controllers\System;

use App\Models\Product;
use App\Models\StaticMenu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class SystemController extends Controller
{
    public function runSchedule(): void
    {
//        Artisan::call('queue:work');
        Artisan::call('schedule:run');
    }

    public function configureEmailView()
    {
        return view('email_configuration');
    }

    public function configureEmail(Request $request)
    {
        putenv("hey=me");
//        putenv("MAIL_MAILER=".$request->mailer);
//        putenv("MAIL_HOST=".$request->host);
//        putenv("MAIL_PORT=".$request->port);
//        putenv(['MAIL_USERNAME' => $request->username]);
//        putenv(['MAIL_PASSWORD' => $request->password]);
//        putenv(['MAIL_ENCRYPTION' => $request->encryption]);
//        putenv(['MAIL_FROM_ADDRESS' => $request->email]);
//        putenv(['MAIL_FROM_NAME' => $request->name]);
    }

    public function sendCaptcha()
    {
        return response()->json([
            'status'  => true,
            'captcha' => Captcha::create('default',true)
        ]);
    }

    public function cache()
    {
        Artisan::call('cache:clear');

        return response()->json([
            'status' => true,
        ]);
    }

    public function changeLanguage()
    {
        App::setLocale(request()->lang);

        session()->put('locale', request()->lang);

        return response()->json([
            'status' => true,
        ]);
    }

    public function clearLogs()
    {
        file_put_contents(storage_path('logs/laravel.log'),'');
    }

    public function generateSitemap()
    {
        $map = SitemapGenerator::create(env('FRONTEND_URL'))
            ->getSitemap()
            ->add(Url::create(env('FRONTEND_URL') . '/')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(1)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/faq')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/contact')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/login')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/register')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/cart')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/compare')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/wishlist')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/profile')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/profile/shipping')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/profile/order')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/profile/history')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.5)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/products')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.9)
            )
            ->add(Url::create(env('FRONTEND_URL') . '/checkout')
                ->setLastModificationDate(Carbon::now('Asia/Dhaka'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6)
            );

        Product::get()->each(function ($product) use ($map) {
            $map->add(Url::create(env('FRONTEND_URL') . "/details/{$product->id}/{$product->slug}")
                ->setLastModificationDate($product->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7)
            );
        });

        StaticMenu::get()->each(function ($menu) use ($map) {
            $map->add(Url::create(env('FRONTEND_URL') . "/page/{$menu->id}")
                ->setLastModificationDate($menu->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.8)
            );
        });

        $map->writeToFile(public_path('sitemap.xml'));

        dd('sitemap created for: '. env('FRONTEND_URL') .'.');
    }
}
