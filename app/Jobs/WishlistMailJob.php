<?php

namespace App\Jobs;

use App\Mail\SendWishListMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class WishlistMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $to, $wishlist;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $wishlist)
    {
        $this->to = $to;
        $this->wishlist = $wishlist;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Mail::to($this->to)->send(new SendWishListMail($this->wishlist));
        } catch (\Throwable $th) {}
    }
}
