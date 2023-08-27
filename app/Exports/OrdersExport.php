<?php

namespace App\Exports;

use App\Http\Services\OrderService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Order;

class OrdersExport implements FromCollection, WithHeadings
{
    /**
    * @return Collection
    */
    public function collection()
    {
        $data = (new OrderService(new Order()))->getOrderList(false);

        $result = array();

        foreach ($data as $key=>$item)
        {
            $result[$key] = array(
                '#'        => $key + 1,
                'id'       => $item->order_number,
                'name'     => $item->name,
                'phone'    => $item->shipping_phone,
                'amount'   => $item->total_amount,
                'p_status' => $item->payment_status,
                'o_status' => $item->order_status,
                'time'     => $item->created_at
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            '#','Order ID','Customer','Contact','Amount','Payment Status','Order Status','Purchased On'
        ];
    }
}
