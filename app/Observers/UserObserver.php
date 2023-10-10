<?php

namespace App\Observers;

use App\Jobs\EmailVerificationMailJob;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Hash;
use App\Models\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function created(User $user)
    {
        if(is_null($user->shop_branch_id))
        {
            $code = rand(100000, 999999);

            EmailVerification::create([
                'user_id'               => $user->id,
                'verification_token'    => Hash::make($code),
                'expired_at'            => Carbon::now()->addMonth()
            ]);

//            sending verification code via email (using queue)
//            dispatch(new EmailVerificationMailJob($user, $code));
            Mail::to($user->username)->send(new EmailVerificationMail($user, $code));


        }
    }
}
