<?php

namespace App\Console\Commands;

use App\Models\FlashSale;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FlashSalesStopCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashsale:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $FlashSale = FlashSale::first();

        if ($FlashSale) {
            if ($FlashSale->status == 1 && $FlashSale->end_date < Carbon::now('Asia/Dhaka')) {
                $FlashSale->status = 0;
                $FlashSale->save();
            }
        }
    }
}
