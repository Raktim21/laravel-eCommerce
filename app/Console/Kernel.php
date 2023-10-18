<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('order:status')->everyMinute();
        $schedule->command('flashsale:status')->everyMinute();
        $schedule->command('order:review')->everyMinute();
//        $schedule->command('email:verify')->everyMinute();
        $schedule->command('order:placed')->everyMinute();
        $schedule->command('check:stock')->everySixHours();
//        $schedule->command('queue:work', [ '--max-time' => 300])->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
