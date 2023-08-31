<?php

namespace App\Observers;

use App\Http\Services\GeneralSettingService;
use App\Models\GeneralSetting;
use App\Models\PromoCode;
use App\Models\MessengerSubscriptions;

class PromoCodeObserver
{
    public function created(PromoCode $code)
    {
        if($code->is_global_user == 1)
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
                        'coupon_code'       => $code->code,
                        'title'             => $code->title,
                        'description'       => $code->is_global_product == 1 ? 'Applicable for all products.' :
                            'Applicable for specific products only.'
                    );

                    sendMessengerResponse($payload, 'offers');
                }
            }
        }
    }
}
