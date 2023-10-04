<?php

namespace App\Observers;

use App\Http\Services\GeneralSettingService;
use App\Jobs\PromoCodeJob;
use App\Models\GeneralSetting;
use App\Models\PromoCode;
use App\Models\MessengerSubscriptions;

class PromoCodeObserver
{
    public function created(PromoCode $code)
    {
        if($code->is_global_user == 1)
        {
//            notifying everyone who subscribed via messenger

            dispatch(new PromoCodeJob($code));
        }
    }
}
