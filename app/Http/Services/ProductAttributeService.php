<?php

namespace App\Http\Services;

use App\Models\Inventory;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCombination;
use App\Models\ProductCombinationValue;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAttributeService
{
    public function saveAttribute(Request $request)
    {
        DB::beginTransaction();

        $values = null;

        try {
            $attribute = ProductAttribute::create([
                'product_id'    => $request->product_id,
                'name'          => $request->name,
            ]);

            foreach ($request->values as $item) {
                ProductAttributeValue::create([
                    'product_attribute_id'  => $attribute->id,
                    'name'                  => $item
                ]);
            }

            foreach($request->combinations as $item)
            {
                $values = array_map(function($value) {
                    return $value['name'];
                }, $item['values']);

                $values = implode(',', $values);

                $combo = !is_null($item['id']) ? ProductCombination::find($item['id']) : null;

                if(is_null($combo))
                {
                    $combo = ProductCombination::create([
                        'product_id'      => $request->product_id,
                        'selling_price'   => $item['selling_price'],
                        'cost_price'      => $item['cost_price'],
                        'weight'          => $item['weight'],
                        'is_default'      => $item['is_default'],
                        'is_active'       => $item['inactive'] == 1 ? 0 : 1
                    ]);
                } else {
                    $combo->update([
                        'selling_price'   => $item['selling_price'],
                        'cost_price'      => $item['cost_price'],
                        'weight'          => $item['weight'],
                        'is_default'      => $item['is_default'],
                        'is_active'       => $item['inactive'] == 1 ? 0 : 1
                    ]);
                }

                $newValue = array_filter($item['values'], function ($value) {
                    return $value['id'] === null;
                });

                $oldValue = array_filter($item['values'], function ($value) {
                    return $value['id'] !== null;
                });

                if(is_null($item['id']))
                {
                    foreach ($oldValue as $old)
                    {
                        ProductCombinationValue::create([
                            'combination_id'    => $combo->id,
                            'att_value_id'      => $old['id'],
                        ]);
                    }
                }

                if(!is_null($item['quantity']))
                {
                    Inventory::withTrashed()->updateOrCreate([
                        'product_combination_id'    => $combo->id,
                        'shop_branch_id'            => auth()->guard('admin-api')->user()->shop_branch_id,
                    ],[
                        'stock_quantity'            => $item['quantity'],
                    ]);
                }

                if($item['inactive'] == 1)
                {
                    $combo->inventory()->delete();
                } else {
                    $combo->inventory()->restore();
                }

                $newValue = reset($newValue)['name'];

                ProductCombinationValue::create([
                    'combination_id'    => $combo->id,
                    'att_value_id'      => ProductAttributeValue::where('name',$newValue)
                        ->where('product_attribute_id', $attribute->id)->first()->id,
                ]);
            }

            DB::commit();
            return 'done';
        } catch(QueryException $ex) {
            DB::rollback();
            return 'Duplicate combination detected of attribute values: ' . $values;
        }
    }

}
