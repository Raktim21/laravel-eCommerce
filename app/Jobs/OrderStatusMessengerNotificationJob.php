<?php

namespace App\Jobs;

use App\Http\Services\GeneralSettingService;
use App\Models\GeneralSetting;
use App\Models\MessengerSubscriptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderStatusMessengerNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subscription = MessengerSubscriptions::where('user_id', $this->order->user_id)
            ->where('subscription_type_id', 2)->first();

        if(!is_null($subscription)) {
            $payload = array(
                'psid'          => $subscription->user->profile->messenger_psid,
                'page_id'       => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
                'order_status'  => $this->order->delivery_status,
                'order_no'      => $this->order->order_number
            );
            sendMessengerResponse($payload, 'order_status');
        }
    }
}
