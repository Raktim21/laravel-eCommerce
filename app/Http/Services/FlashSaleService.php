<?php

namespace App\Http\Services;

use App\Models\FlashSale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashSaleService
{
    public function getSale()
    {
        return array(
            'flash_sale'    => FlashSale::first(),
            'products'      => Product::where('is_on_sale', 1)
                ->select('id','category_id','name','thumbnail_image','display_price','uuid','slug')
                ->with(['category' => function($q) {
                    return $q->select('id','name');
                }])->get()
        );
    }

    public function updateSale(Request $request)
    {
        DB::beginTransaction();

        try {
            Product::where('is_on_sale', 1)->update(['is_on_sale' => 0]);

            $sale = FlashSale::updateOrCreate(
                [
                    'id' => 1
                ],
                [
                    'title' => $request->title,
                    'short_description' => $request->short_description,
                    'start_date' => Carbon::parse($request->start_date),
                    'end_date' => Carbon::parse($request->end_date),
                    'discount' => $request->discount
                ]
            );

            if($request->hasFile('image')) {
                saveImage($request->file('image'), '/uploads/images/banner/', $sale, 'image');
            }

            Product::whereIn('id', $request->products)->update(['is_on_sale' => 1]);

            DB::commit();

            return true;
        } catch (QueryException $ex)
        {
            return false;
        }
    }

    public function updateSaleStatus(): bool
    {
        $sale = FlashSale::first();

        if($sale)
        {
            if($sale->status == 0 && $sale->end_date < Carbon::now())
            {
                return false;
            }
            $status = $sale->status == 0 ? 1 : 0;

            $sale->update(['status' => $status]);

            return true;
        }

        return false;
    }

}
