<?php

namespace App\Observers;

use App\Mail\EmailVerificationMail;
use App\Models\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
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

            try {
                Mail::to($user->username)->queue(new EmailVerificationMail($user, $code));
            } catch (\Throwable $th) {}
        }
    }
}
