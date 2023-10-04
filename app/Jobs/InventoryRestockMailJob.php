<?php

namespace App\Jobs;

use App\Mail\InventoryRestockMail;
use App\Models\ProductRestockRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class InventoryRestockMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $inventory;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $requests = ProductRestockRequest::where('product_id', $this->inventory->combination->product_id)
            ->where('is_stocked',0)->get();

        foreach ($requests as $request)
        {
            try {
                $mail_data = array(
                    'user'      => $request->user->name,
                    'product'   => $this->inventory->combination->product->name,
                    'slug'      => $this->inventory->combination->product->slug,
                    'stock'     => $this->inventory->stock_quantity,
                    'pr_id'     => $this->inventory->combination->product->id
                );

                $to = $request->user->username;

                Mail::to($to)->send(new InventoryRestockMail($mail_data));
            }
            catch (\Throwable $th) {}

            $request->delete();
        }
    }
}
