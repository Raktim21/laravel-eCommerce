<html>
<head>
    <title>
        Invoice | {{ $title }}
    </title>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            text-align: left;
            padding: 10px;
        }
    </style>
</head>
<body>
<section>
    <div style="float: left; margin-top: 20px">
        <b style="font-size: 20px">Issued To:</b>
        <p style="font-size: 17px">{{ $order['user']['name'] }}</p>
        <p style="font-size: 13px">
            Email: {{ $order['user']['username'] }} <br>
            @if(isset($order['delivery_address']['phone_no']))
            Contact: {{ $order['delivery_address']['phone_no'] }} <br>
            @endif
            <b>Purchased On: </b>{{ $order['created_at'] }}
        </p>
    </div>
    <div style="float: right">
        @if($general['logo'] != '')
        <img style="height: 80px" src="{{ public_path($general['logo']) }}">
        @endif
        <p style="font-size: 11px">{{ $general['address'] }}</p>
        <p>
            <b>Invoice No: </b>{{ $order['order_number'] }}
        </p>
    </div>
</section>

<div style="background-color: brown; height: 1px; width: 100%; margin-top: 170px"></div>

<section style="margin-top: 30px">
    <table>
        <tr style="background-color: #374151;color: #fff;">
            <td>Product Name</td>
            <td>Quantity</td>
            <td>Price(Tk)</td>
            <td>Amount(Tk)</td>
        </tr>

            @foreach($order['items'] as $item)
            <tr class="table_body">
                <td style="color: #000">{{ $item['combination']['product']['name'] }}
                    -
                    @foreach($item['combination']['attributeValues'] as $key=>$val)
                        @if($key!=0),@endif {{ $val['name'] }}
                    @endforeach
                </td>
                <td>{{ $item['product_quantity'] }}</td>
                <td>{{ $item['product_price'] }}</td>
                <td>{{ $item['total_price'] }}</td>
            </tr>
            @endforeach
    </table>
    <div style="background-color: #374151; height: 1px; width: 100%;"></div>
    <div style="float: right; margin-right: 27px">
        <p>
            <span style="margin-right: 140px">Subtotal</span> Tk {{ $order['sub_total_amount'] }}<br>
            <span style="margin-right: 135px">Discount</span> Tk {{ $order['promo_discount'] }}<br>
            <span style="margin-right: 102px">Shipping Cost</span> Tk {{ $order['delivery_cost'] }}<br>
            <span style="margin-right: 74px">Additional Charge</span> Tk {{ $order['additional_charges'] }}<br>
            <span style="margin-right: 160px">Total</span> Tk {{ $order['total_amount'] }}
        </p>
    </div>
</section>
<section style="position: absolute; bottom: 0; width: 100%">
    <div style="background-color: brown; height: 1px; width: 100%"></div>
    <p>Email: <span>{{ $general['email'] }}</span><br>Phone: {{ $general['phone'] }}</p>
</section>
</body>
</html>
