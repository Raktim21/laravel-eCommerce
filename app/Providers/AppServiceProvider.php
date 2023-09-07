<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use Symfony\Component\Console\Output\ConsoleOutput;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('max_float', function ($attribute, $value, $parameters, $validator) {
            $maxValue = (float) $parameters[0];
            return $value <= $maxValue;
        });

        Validator::replacer('max_float', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':max', $parameters[0], $message);
        });

        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();

        // DB::listen(function ($query) use ($out) {
        //     $out->writeln($query->sql . ' - ' . $query->time);
        // });

        // DB::listen(function ($query) use ($out) {
        //     $out->writeln($query->sql . ' - ' . $query->time);
        // });

        LogViewer::auth(function ($request) {
            return request()->header('Authorization') == 'WoPnaBmQmEinDWxrVfRkzcOOEuTJSxLWqBaxwnLMsQnIokdNayMqwvDmRzrB';
        });
    }
}

