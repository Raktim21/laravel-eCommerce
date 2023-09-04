<?php

namespace App\Observers;

use App\Mail\InventoryRestockMail;
use App\Models\Inventory;
use App\Models\ProductRestockRequest;
use Illuminate\Support\Facades\Cache;
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
                    $mail_data = array(
                        'user'      => $request->user->name,
                        'product'   => $inventory->combination->product->name,
                        'slug'      => $inventory->combination->product->slug,
                        'stock'     => $inventory->stock_quantity,
                        'pr_id'     => $inventory->combination->product->id
                    );

                    $to = $request->user->username;

                    Mail::to($to)->queue(new InventoryRestockMail($mail_data));
                }
                catch (\Throwable $th) {}

                $request->delete();
            }
        }
    }
}
