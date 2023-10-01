<?php

namespace App\Observers;

use App\Jobs\InventoryRestockMailJob;
use App\Models\Inventory;

class InventoryObserver
{
    private $previous_quantity;
    public function updating(Inventory $inventory)
    {
        $this->previous_quantity = $inventory->getOriginal('stock_quantity');
    }

    public function updated(Inventory $inventory)
    {
//        when a product is restocked, deleting the requests and sending emails (using queue)

        if($inventory->stock_quantity > $this->previous_quantity)
        {
            dispatch(new InventoryRestockMailJob($inventory));
        }
    }
}
