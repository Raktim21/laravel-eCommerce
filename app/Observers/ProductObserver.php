<?php

namespace App\Observers;

use App\Models\AttributeVariant;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCombination;
use App\Models\ProductCombinationValue;
use App\Models\ProductCombinationVariant;
use App\Models\ProductMeta;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param Product $product
     * @return void
     */
    public function created(Product $product)
    {
        if(request()->has('attribute_list'))
        {
            $attribute_list = json_decode(request()->input('attribute_list'), true);

            foreach($attribute_list as $attr) {
                $product_attribute = ProductAttribute::create(['product_id' => $product->id, 'name' => $attr['name']]);

                foreach ($attr['values'] as $variant) {
                    ProductAttributeValue::create([
                        'product_attribute_id' => $product_attribute->id,
                        'name'                 => $variant
                    ]);
                }
            }

            $combinations = $this->generateCombinations($attribute_list);

            foreach($combinations as $key => $combo) {
                $product_combo = ProductCombination::create([
                    'product_id'        => $product->id,
                    'cost_price'        => request()->input('cost_price'),
                    'selling_price'     => request()->input('display_price'),
                    'weight'            => request()->input('weight'),
                    'is_default'        => $key===0 ? 1 : 0
                ]);

                foreach ($combo as $value) {
                    ProductCombinationValue::create([
                        'combination_id'    => $product_combo->id,
                        'att_value_id'      => ProductAttributeValue::whereHas('attribute', function ($q) use ($product) {
                            $q->where('product_id', $product->id);
                        })
                            ->where('name',$value)->first()->id,
                    ]);
                }
            }
        } else {
            $product_attribute = ProductAttribute::create(['product_id' => $product->id, 'name' => 'default']);

            $value = ProductAttributeValue::create([
                'product_attribute_id' => $product_attribute->id,
                'name'                 => 'default'
            ]);

            $product_combo = ProductCombination::create([
                'product_id'        => $product->id,
                'cost_price'        => request()->input('cost_price'),
                'selling_price'     => request()->input('display_price'),
                'weight'            => request()->input('weight'),
                'is_default'        => 1
            ]);

            ProductCombinationValue::create([
                'combination_id'    => $product_combo->id,
                'att_value_id'      => $value->id,
            ]);
        }

        if(request()->input('meta_title') || request()->input('meta_description') || request()->input('meta_keywords')) {
            ProductMeta::create([
                'product_id'         => $product->id,
                'meta_title'         => request()->input('meta_title') ?? 'N/A',
                'meta_description'   => request()->input('meta_description') ?? 'N/A',
                'meta_keywords'      => request()->input('meta_keywords') ?? 'N/A',
                'meta_image'         => ''
            ]);
        }

        Artisan::call('cache:clear');
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
        $variants = $currentAttribute['values'];

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
                    $currentAttribute['name'] => $variant,
                ], $nextCombination);

                // Add the combined combination to the list of all combinations
                $allCombinations[] = $combinedCombination;
            }
        }

        return $allCombinations;
    }
}
