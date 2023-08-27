<?php

namespace App\Exports;

use App\Http\Services\InventoryService;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryExport implements FromCollection, WithHeadings
{
    /**
    * @return Collection
    */
    public function collection()
    {
        $data = (new InventoryService())->inventoryList(0);

        $result = array();

        foreach ($data as $key=>$item)
        {
            $result[$key] = array(
                '#' => $key + 1,
                'code' => $item->combination->product->uuid,
                'name' => $item->combination->product->name,
                'attr' => collect($item->combination->attributeValues)->pluck('name')->join(','),
                'price' => $item->combination->selling_price,
                'weight' => $item->combination->weight,
                'stock' => $item->stock_quantity,
                'damage' => $item->damage_quantity,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            '#','Code','Name','Attribute','Price','Weight','In Stock','Damage Count'
        ];
    }
}
