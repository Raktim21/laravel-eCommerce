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

class PromoCodeUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $exist = MessengerSubscriptions::where('user_id', $this->user->user_id)->where('subscription_type_id', 1)->first();

        if(!is_null($exist))
        {
            $psid = $this->user->user->profile->messenger_psid;

            if(!is_null($psid))
            {
                $payload = array(
                    'sender_id'     => $psid,
                    'page_id'       => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
                    'coupon_code'   => $this->user->promo->code,
                    'title'         => $this->user->promo->title,
                    'description'   => $this->user->promo->is_global_product == 1 ? 'Applicable for all products.' :
                        'Applicable for specific products only.'
                );

                sendMessengerResponse($payload, 'offers');
            }
        }
    }
}
