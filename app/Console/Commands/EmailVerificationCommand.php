<?php

namespace App\Console\Commands;

use App\Mail\VerificationWarningMail;
use App\Models\EmailVerification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailVerificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:verify';

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
        $emails = EmailVerification::whereDate('expired_at', Carbon::now()->format('Y-m-d'))->get();

        foreach ($emails as $email)
        {
            if(date_format($email->created_at, 'Y-m-d') <= Carbon::now()->subMonths(2)->format('Y-m-d'))
            {
                $email->user->delete();
                $email->delete();
            } else {
                try {
                    Mail::to($email->user->username)->send(new VerificationWarningMail($email->user));
                } catch (\Throwable $th) {}
            }
        }
    }
}
