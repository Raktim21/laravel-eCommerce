<?php

namespace App\Console\Commands;

use App\Mail\OrdersMail;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderPlacementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:placed';

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
        $orders = Order::with(['items.combination' => function($q) {
                return $q->with(['product' => function($q1) {
                    return $q1->select('id','name')->withTrashed();
                }])->with(['attributeValues' => function($q1) {
                    return $q1->withTrashed();
                }])->withTrashed();
            }])
            ->whereBetween('created_at',[Carbon::now('Asia/Dhaka')->subMinutes(5), Carbon::now('Asia/Dhaka')])
            ->where('order_status_id', 1)
            ->get();

        foreach ($orders as $order)
        {
            try {
                $to = $order->user->username;

                $mail_data = [
                    'user' => $order->user->name,
                    'order' => $order
                ];

                Mail::to($to)->send(new OrdersMail($mail_data));
            } catch (\Throwable $th) {
                Log::info($order->order_number. ' - ' .$th->getMessage());
            }
        }
    }
}
