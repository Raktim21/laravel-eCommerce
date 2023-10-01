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

class PromoCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $code;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $exist_users = MessengerSubscriptions::where('subscription_type_id', 1)->get();

        foreach ($exist_users as $user)
        {
            $psid = $user->user->profile->messenger_psid;
            if(!is_null($psid))
            {
                $payload = array(
                    'sender_id'         => $psid,
                    'page_id'           => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
                    'coupon_code'       => $this->code->code,
                    'title'             => $this->code->title,
                    'description'       => $this->code->is_global_product == 1 ? 'Applicable for all products.' :
                        'Applicable for specific products only.'
                );

                sendMessengerResponse($payload, 'offers');
            }
        }
    }
}
