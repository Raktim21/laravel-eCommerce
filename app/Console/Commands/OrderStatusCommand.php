<?php

namespace App\Console\Commands;

use App\Http\Services\AssetService;
use App\Http\Services\OrderDeliverySystemService;
use App\Models\GeneralSetting;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use App\Models\Order;
use GuzzleHttp\Client;

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
            $order = Order::where('delivery_system_id', 2)
                ->where('order_status_id', 2)->get();

            $client = new Client();

            foreach ($order as $order_status)
            {
                try {
                    $response = $client->post(eCourier()['url'] . '/track-child', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'API-SECRET'   => eCourier()['api_secret'],
                            'API-KEY'      => eCourier()['api_key'],
                            'USER-ID'      => eCourier()['user_id']
                        ],
                        'json' => ["ecr" => $order_status->delivery_tracking_number],
                    ]);

                    $data = json_decode($response->getBody()->getContents(), true);


                    if (count($data['query_data']['status']) > 0) {

                        $order_status->delivery_status = $data['query_data']['status'][0]['status'];

                        if ($data['query_data']['status'][0]['status'] == 'Delivered')
                        {
                            $order_status->order_status_id = 4;
                            $order->payment_status_id = 2;
                            $order_status->paid_amount = $order_status->total_amount;
                        }
                    }

                    $order_status->save();
                }
                catch (\Exception $e) {}
            }
    }
}
