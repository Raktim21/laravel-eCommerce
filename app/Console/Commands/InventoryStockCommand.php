<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use App\Notifications\InventoryRestockNotification;
use Illuminate\Console\Command;

class InventoryStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:stock';

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
        $branches = Branch::orderBy('id')->get();

        try {
            foreach ($branches as $branch) {

                $stock = Product::whereHas('inventories', function ($q) use ($branch) {
                    return $q->where('shop_branch_id', $branch->id)
                        ->where('stock_quantity', 0);
                })->get();

                if (!is_null($stock))
                {
                    $stock = json_decode($stock);

                    $alert = '';

                    if (count($stock) == 1) {
                        $alert = $stock[0]->name . ' has been stocked out.';
                    } else {
                        $alert .= $stock[0]->name . ' and ' . count($stock) - 1 . ' more products have been stocked out.';
                    }

                    $admins = User::where('shop_branch_id', $branch->id)->get();

                    foreach ($admins as $admin) {
                        $admin->notify(new InventoryRestockNotification($alert));
                    }
                }
            }
        } catch(\Throwable $th)
        {}
    }
}
