<?php

namespace App\Console\Commands;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OrderStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:status';

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
     * @throws GuzzleException
     */
    public function handle()
    {
        $order = Order::where('order_status_id',2)->get();
        $client = new Client();

        foreach ($order as $order_status) {

            try {

                $response = $client->post(peperfly()['paperFlyUrl'].'/API-Order-Tracking/', [
                    'headers' => [
                        'paperflykey' => peperfly()['paperFlyKey']
                    ],
                    'auth' =>  peperfly()['credential'],
                    'json' => ["ReferenceNumber"=> $order_status->order_number],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if ($data['success']['trackingStatus'][0]['Delivered'] == '') {

                    if ($data['success']['trackingStatus'][0]['Pick'] == '') {
                        $order_status->delivery_status = 'Not Picked Yet';
                    }else {
                        $order_status->delivery_status = 'Picked';
                    }
                } else {
                    $order_status->delivery_status = 'Delivered';
                    $order_status->order_status_id = 4;
                    $order->payment_status_id      = 2;
                    $order_status->paid_amount     = $order_status->total;
                }

                $order_status->save();


            }catch (\Exception $e) {}
        }
    }
}
