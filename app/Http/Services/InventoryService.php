<?php

namespace App\Http\Services;

use App\Models\Inventory;
use App\Models\InventoryTrace;
use App\Models\InventoryTraceProduct;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryService
{

    public function inventoryList($isPaginated)
    {
        return Inventory::with(['combination' => function($q) {
                $q->with(['product' => function($q) {
                    return $q->select('id','uuid','name','display_price')->withTrashed();
                }])
                    ->with('attributeValues.attribute')->withTrashed();
            }])->when(request()->input('shop_branch_id'), function($q) {
                return $q->where('shop_branch_id', request()->input('shop_branch_id'));
            })
            ->when(!request()->input('shop_branch_id'), function($q) {
                return $q->where('shop_branch_id', auth()->guard('admin-api')->user()->shop_branch_id);
            })
            ->with(['branch' => function($q1) {
                return $q1->select('id','name');
            }])->latest()
            ->when($isPaginated==0, function ($q) {
                return $q->get();
            })
            ->when($isPaginated==1, function ($q) {
                return $q->paginate(20)->appends(request()->except('page'));
            });
    }

    public function updateStock($quantity, $id)
    {
        $inv = Inventory::findOrFail($id);

        if($inv->shop_branch_id != auth()->guard('admin-api')->user()->shop_branch_id &&
            !auth()->guard('admin-api')->user()->hasRole('Super Admin')) {
            return 1;
        }
        $inv->update(['stock_quantity' => $quantity]);

        return 2;
    }

    public function updateDamage(Request $request, $product): bool
    {
        if (($request->type == 'restore') && ($product->damage_quantity > 0)) {

            if ($product->damage_quantity < $request->damage_count) {
                return false;
            }

            $product->stock_quantity += $request->damage_count;
            $product->damage_quantity -= $request->damage_count;

        } elseif ($request->type == 'update') {

            $product->stock_quantity -= $request->damage_count;
            $product->damage_quantity = $request->damage_count;

        }else {

            if (($product->damage_quantity < $request->damage_count)) {
                return false;
            }

            $product->damage_quantity = 0;
        }
        $product->save();
        return true;
    }

    public function transfer(Request $request): bool
    {
        DB::beginTransaction();
        try {
            $trace_id = 0;
            foreach ($request->items as $key => $item)
            {
                $inventory = Inventory::find($item['id']);

                if ($key == 0) {
                    $trace = InventoryTrace::create([
                        'from_branch_id'    => $inventory->shop_branch_id,
                        'to_branch_id'      => $request->to_branch_id,
                        'event_date'        => $request->event_date ? Carbon::parse($request->event_date)->format('Y-m-d') : date('Y-m-d')
                    ]);

                    $trace_id = $trace->id;
                }

                InventoryTraceProduct::create([
                    'trace_id'                  => $trace_id,
                    'product_combination_id'    => $inventory->product_combination_id,
                    'product_quantity'          => $item['quantity']
                ]);

                $to_branch_inventory = Inventory::where('shop_branch_id', $request->to_branch_id)
                    ->where('product_combination_id', $inventory->product_combination_id)->first();

                if(is_null($to_branch_inventory)) {
                    Inventory::create([
                        'shop_branch_id'            => $request->to_branch_id,
                        'product_combination_id'    => $inventory->product_combination_id,
                        'stock_quantity'            => $item['quantity'],
                        'deleted_at'                => $inventory->deleted_at,
                    ]);
                } else {
                    $to_branch_inventory->stock_quantity += $item['quantity'];
                    $to_branch_inventory->save();
                }

                $inventory->stock_quantity -= $item['quantity'];
                $inventory->save();
            }
            DB::commit();
            return true;
        } catch (QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    public function inventoryLog()
    {
        return InventoryTrace::with(['traceProducts' => function($q) {
            return $q->with(['productCombination' => function($q1) {
                return $q1->with(['attributeValues' => function($q3) {
                    return $q3->withTrashed();
                }])
                    ->with(['product' => function($q2) {
                    return $q2->select('id','name','slug','thumbnail_image','deleted_at')
                        ->withTrashed();
                }])->withTrashed();
            }]);
        }])
        ->when(request()->input('shop_branch_id'), function ($q) {
            return $q->where('from_branch_id', request()->input('shop_branch_id'));
        })
        ->when(!request()->input('shop_branch_id'), function ($q) {
            return $q->where('from_branch_id', auth()->guard('admin-api')->user()->shop_branch_id);
        })
        ->with('from_branch','to_branch')
        ->latest()->paginate(15);
    }
}
