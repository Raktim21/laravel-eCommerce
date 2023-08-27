<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductAttributeDeleteRequest;
use App\Http\Requests\ProductAttributeStoreRequest;
use App\Http\Services\ProductAttributeService;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeVariant;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCombination;
use App\Models\ProductCombinationValue;
use App\Models\ProductCombinationVariant;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductAttributeController extends Controller
{

    protected $service;

    public function __construct(ProductAttributeService $service)
    {
        $this->service = $service;
    }

    public function store(ProductAttributeStoreRequest $request)
    {
        if(ProductAttribute::where('product_id', $request->product_id)->count() == 3) {

            return response()->json([
                'status' => false,
                'errors' => ['A product can have only three attributes.']
            ], 400);
        } else {
            $msg = $this->service->saveAttribute($request);

            if($msg == 'done')
            {
                return response()->json([
                    'status' => true,
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'errors'  => [$msg],
                ], 422);
            }
        }
    }

    public function storeVariant(Request $request,$attribute_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:255',
                        Rule::unique('product_attribute_values')->whereNull('deleted_at')
                            ->where(function ($query) use($attribute_id) {
                            return $query->where('product_attribute_id',$attribute_id);
                        })
                      ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $variant = ProductAttributeValue::create([
                'product_attribute_id'=>$attribute_id,
                'name' => $request->name
            ]);

            $this->addNewCombination($variant);

            DB::commit();
            return response()->json([
                'status' => true,
            ], 201);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.']
            ], 500);
        }
    }

    private function addNewCombination($variant)
    {
        $attributes = ProductAttribute::whereNot('id',$variant->product_attribute_id)
            ->where('product_id', $variant->attribute->product_id)->with('attributeValues')->get();
        $new_attribute = ProductAttribute::where('id', $variant->product_attribute_id)
            ->with(['attributeValues' => function($q) use ($variant) {
                $q->where('id', $variant->id);
            }])->get();
        $attributes = json_decode($attributes, true);
        $new_attribute = json_decode($new_attribute, true);
        array_unshift($attributes, $new_attribute[0]);

        $combo = $this->generateCombinations($attributes);

        $default_combo = ProductCombination::where('product_id', $variant->attribute->product_id)
            ->where('is_default', 1)->first();

        foreach($combo as $item) {
            $product_combo = ProductCombination::create([
                'product_id'        => $variant->attribute->product_id,
                'cost_price'        => $default_combo->cost_price,
                'selling_price'     => $default_combo->selling_price,
                'weight'            => $default_combo->weight,
                'is_default'        => 0
            ]);

            foreach ($item as $value) {
                ProductCombinationValue::create([
                    'combination_id'      => $product_combo->id,
                    'att_value_id'        => $value
                ]);
            }
        }
    }


     public function update(Request $request,$id)
     {
         $attr = ProductAttribute::findOrFail($id);

         $validator = Validator::make($request->all(), [
             'name' => ['required','string','max:100',
                 Rule::unique('product_attributes', 'name')
                     ->where('product_id', $attr->product_id)->ignore($id)
             ],
         ]);

         if ($validator->fails()) {
             return response()->json([
                 'status' => false,
                 'errors' => $validator->errors()->all()
             ], 422);
         }

         $attr->update(['name' => $request->name]);

         return response()->json([
             'status' => true,
         ]);
     }


     public function valueDelete($id)
     {
         $variant = ProductAttributeValue::findOrFail($id);

         if($variant->name == 'default')
         {
             return response()->json([
                 'status' => false,
                 'errors' => ['Default product combination value can not be deleted.']
             ], 400);
         }
         DB::beginTransaction();
         try {
             if ($variant->attribute->attributeValues->count() == 1) {
                 return response()->json([
                     'status' => false,
                     'errors' => ['Selected attribute has only one value.']
                 ], 422);
             } else {
                 $combo = ProductCombination::whereHas('attributeValues', function ($q) use ($variant) {
                     return $q->where('att_value_id', $variant->id);
                 })->get();

                 foreach ($combo as $item)
                 {
                     if($item->is_default == 1)
                     {
                         return response()->json([
                             'status' => false,
                             'errors' => ['Default product combination values can not be deleted.']
                         ], 422);
                     }

                     $item->inventory()->forceDelete();
                     $item->wishlistItem()->delete();
                     $item->cart()->delete();
                     $item->delete();
                 }

                 $variant->delete();
             }
             DB::commit();
             return response()->json(['status' => true]);
         } catch (QueryException $e) {
             DB::rollback();
             return response()->json([
                 'status' => false,
                 'errors' => ['Something went wrong.']
             ], 500);
         }
     }

    public function destroy(ProductAttributeDeleteRequest $request)
    {
        $attributeIdToDelete = $request->attribute_to_delete;

        $attributeValuesToDelete = ProductAttributeValue::where('product_attribute_id', $attributeIdToDelete)->pluck('id');

        $combo = ProductCombination::select('id')->where('product_id', $request->product_id)
            ->with(['attributeValues' => function($q) use ($attributeIdToDelete) {
                return $q->whereNot('product_attribute_id', $attributeIdToDelete);
            }])
            ->get()
            ->groupBy(function ($item) {
                return $item->attributeValues->pluck('id');
            });

        $status = $this->validateCombinations($combo, $request->combinations);

        if(!$status)
        {
            return response()->json([
                'status' => false,
                'errors' => ['One or more required combinations are missing.']
            ], 422);
        }

        DB::beginTransaction();

        try {
            $requested_combinations = array_map(function($combination) {
                return $combination['id'];
            }, $request->combinations);

            ProductAttribute::find($attributeIdToDelete)->delete();
            ProductAttributeValue::where('product_attribute_id', $attributeIdToDelete)->delete();

            ProductCombination::where('product_id', $request->product_id)
                ->whereNotIn('id', $requested_combinations)->delete();

            foreach ($request->combinations as $combo)
            {
                $product_combo = ProductCombination::find($combo['id']);

                $product_combo->update([
                    'selling_price'     => $combo['selling_price'],
                    'cost_price'        => $combo['cost_price'],
                    'weight'            => $combo['weight'],
                    'is_default'        => $combo['is_default'],
                    'is_active'         => !is_null($combo['quantity']) ? 1 : 0
                ]);

                if(!is_null($combo['quantity']))
                {
                    Inventory::withTrashed()->updateOrCreate([
                        'product_combination_id'    => $combo['id'],
                        'shop_branch_id'            => auth()->guard('admin-api')->user()->shop_branch_id,
                    ],[
                        'stock_quantity'            => $combo['quantity'],
                    ]);

                    $product_combo->inventory()->restore();
                }

            }
            ProductCombinationValue::whereIn('att_value_id',$attributeValuesToDelete)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
            ]);
        } catch (QueryException $ex)
        {
            DB::rollback();
            return response()->json([
                'status' => false,
                'errors' => ['Something went wrong.']
            ], 500);
        }
    }

    private function validateCombinations($existing_combinations, $requested_combinations): bool
    {
        if(count($requested_combinations) !== count($existing_combinations))
        {
            return false;
        }
        foreach ($requested_combinations as $combo)
        {
            $search_id = $combo['id'];
            $found_index = null;

            foreach ($existing_combinations as $index => $existing)
            {
                foreach ($existing as $item)
                {
                    if($item['id'] === $search_id)
                    {
                        $found_index = $index;
                        break 2;
                    }
                }
            }
            if(!is_null($found_index))
            {
                unset($existing_combinations[$found_index]);
            } else {
                return false;
            }
        }
        return true;
    }


    public function updateVariant(Request $request, $id)
    {
        $variant = ProductAttributeValue::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:255',
                Rule::unique('product_attribute_values', 'name')
                    ->where('product_attribute_id', $variant->product_attribute_id)->ignore($id)
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $variant->update(['name' => $request->name]);

        return response()->json([
            'status' => true,
        ]);
    }


    public function generateCombinations($attributes, $currentIndex = 0, $currentCombination = [])
    {
        $totalAttributes = count($attributes);

        // if the current index is equal to the total number of attributes, add the current combination
        if ($currentIndex === $totalAttributes) {
            return [$currentCombination];
        }

        $currentAttribute = $attributes[$currentIndex];

        // Get the variants of the current attribute
        $variants = $currentAttribute['attribute_values'];

        // Initialize an empty array to store all combinations for the current attribute
        $allCombinations = [];

        // Recursively generate combinations for the next attribute
        $nextIndex = $currentIndex + 1;
        $nextCombinations = $this->generateCombinations($attributes, $nextIndex);

        // Iterate over the variants of the current attribute
        foreach ($variants as $variant) {
            // Combine the current variant with all combinations of the next attributes
            foreach ($nextCombinations as $nextCombination) {
                $combinedCombination = array_merge($currentCombination, [
                    $currentAttribute['name'] => $variant['id'],
                ], $nextCombination);

                // Add the combined combination to the list of all combinations
                $allCombinations[] = $combinedCombination;
            }
        }

        return $allCombinations;
    }




    public function updateCombination(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cost_price'        => 'required|numeric',
            'selling_price'     => 'required|numeric',
            'weight'            => 'required|numeric|max:5',
            'is_default'        => 'sometimes|in:1',
            'stock_quantity'    => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $combo = ProductCombination::withTrashed()->findOrFail($id);

        DB::beginTransaction();

        try {
            if($request->is_default == 1) {
                ProductCombination::where('product_id', $combo->product_id)
                    ->where('is_default',1)->update(['is_default' => 0]);
            }

            $combo->update([
                'cost_price'         => $request->cost_price,
                'selling_price'      => $request->selling_price,
                'weight'             => $request->weight,
                'is_default'         => $request->is_default ?? $combo->is_default,
                'is_active'          => 1
            ]);

            $combo->inventory()->restore();

            Inventory::updateOrCreate([
                'shop_branch_id'                    => auth()->user()->shop_branch_id,
                'product_combination_id'            => $id
            ], [
                'stock_quantity'                    => $request->stock_quantity,
            ]);

            DB::commit();

            return response()->json(['status' => true]);
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function inactiveCombination($id)
    {
        $combo = ProductCombination::findOrFail($id);

        if($combo->is_default == 1)
        {
            return response()->json([
                'status'    => false,
                'errors'    => ['Default combination can not be deactivated.']
            ], 422);
        }

        $combo->inventory()->delete();

        $combo->update(['is_active' => 0]);

        return response()->json([
            'status'    => true,
        ]);
    }

    public function activateCombination($id)
    {
        $combo = ProductCombination::findOrFail($id);

        if($combo->inventory()->withTrashed()->count() == 0)
        {
            return response()->json([
                'status'    => false,
                'errors'    => ['No Inventory found for this product combination.']
            ], 422);
        }

        $combo->inventory()->restore();

        $combo->update(['is_active' => 1]);

        return response()->json([
            'status'    => true,
        ]);
    }
}

