<?php

namespace App\Console\Commands;

use App\Http\Services\GeneralSettingService;
use App\Models\GeneralSetting;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OrderReviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:review';

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
        $orders = Order::where('order_status_id', 4)->whereHas('user.profile', function ($q) {
            return $q->whereNotNull('messenger_psid');
        })->whereBetween('updated_at',[Carbon::now('Asia/Dhaka')->subMinutes(30), Carbon::now('Asia/Dhaka')])
            ->whereHas('items', function ($q) {
                $q->where('is_reviewed', 1);
            }, '=', 0)
            ->get();

        foreach ($orders as $item)
        {
            try {
                $payload = array(
                    'page_id' => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
                    'psid' => $item->user->profile->messenger_psid,
                    'order_id' => $item->order_number
                );

                sendMessengerResponse($payload, 'order_review');
            } catch (\Throwable $th) {}
        }
    }
}
