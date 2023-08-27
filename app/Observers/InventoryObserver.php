<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\ProductRestockRequest;
use Illuminate\Support\Facades\Mail;

class InventoryObserver
{
    private $previous_quantity;
    public function updating(Inventory $inventory)
    {
        $this->previous_quantity = $inventory->getOriginal('stock_quantity');
    }

    public function updated(Inventory $inventory)
    {
        if($inventory->stock_quantity > $this->previous_quantity)
        {
            $requests = ProductRestockRequest::where('product_id', $inventory->combination->product_id)
                ->where('is_stocked',0)->get();

            foreach ($requests as $request)
            {
                try {
                    $mail_body = 'Dear Customer, ' . PHP_EOL . '
                    You have previously requested to restock a product: ' . $inventory->combination->product->name .
                        'The product has been restocked.' . PHP_EOL . '
                    Regards, ' . PHP_EOL . 'Selopia Ecommerce Team';

                    $to = $request->user->username;

                    Mail::raw($mail_body, function ($msg) use ($to) {
                        $msg->to($to)
                            ->subject('Requested product has been restocked');
                    });
                } catch (\Throwable $th) {}

                $request->update(['is_stocked' => 1]);
            }
        }

        if($inventory->stock_quantity == 0)
        {
            $inventory->delete();
        }
    }
}
