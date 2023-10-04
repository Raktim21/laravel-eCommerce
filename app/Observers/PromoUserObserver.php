<?php

namespace App\Observers;

use App\Http\Services\GeneralSettingService;
use App\Jobs\PromoCodeUserJob;
use App\Models\GeneralSetting;
use App\Models\MessengerSubscriptions;
use App\Models\PromoUser;

class PromoUserObserver
{
    public function created(PromoUser $user)
    {
        dispatch(new PromoCodeUserJob($user));
    }
}
