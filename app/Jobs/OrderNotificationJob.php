<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $admins = User::whereNotNull('shop_branch_id')->get();

//        notify admins about order placement/delivery

        foreach ($admins as $admin) {
            $admin->notify(new AdminNotification(
                'Order',
                '/order/details/'.$this->order->id,
                $this->order->order_status_id == 1 ? 'You have a new order from '.$this->order->user->name :
                    'Order ID: '. $this->order->order_number .' has been successfully delivered.',
            ));
        }
    }
}
