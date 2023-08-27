<?php

namespace App\Observers;

use App\Http\Services\GeneralSettingService;
use App\Models\GeneralSetting;
use App\Models\MessengerSubscriptions;
use App\Models\PromoUser;

class PromoUserObserver
{
    public function created(PromoUser $user)
    {
        $exist = MessengerSubscriptions::where('user_id', $user->user_id)->where('subscription_type_id', 1)->first();

        if(!is_null($exist))
        {
            $psid = $user->user->profile->messenger_psid;

            if(!is_null($psid))
            {
                $payload = array(
                    'sender_id'     => $psid,
                    'page_id'       => (new GeneralSettingService(new GeneralSetting()))->getSetting()->facebook_page_id,
                    'coupon_code'   => $user->promo->code,
                    'title'         => $user->promo->title,
                );

                sendMessengerResponse($payload, 'offers');
            }
        }
    }
}
