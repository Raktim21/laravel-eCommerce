<html>
<head>
    <title>
        Billing Invoice | {{ $title }}
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
        <p style="font-size: 17px">{{ $cart['user']['name'] ?? $cart['guest']['name'] }}</p>
        <p style="font-size: 13px">
            Email: {{ $cart['user']['username'] ?? ($cart['guest']['email'] ?? 'N/A') }} <br>
            Contact: {{ $cart['user']['phone'] ?? ($cart['guest']['phone'] ?? 'N/A') }} <br>
            <b>Issued On: </b>{{ $cart['created_at'] }}
        </p>
    </div>
    <div style="float: right">
        @if($general['logo'] != '')
        <img style="height: 80px" src="{{ public_path($general['logo']) }}">
        @endif
        <p style="font-size: 11px">{{ $general['address'] }}</p>
        <p>
            <b>Invoice No: </b>{{ $cart['billing_number'] }}
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

        @php
        $total = 0;
        @endphp

        @foreach($cart['items'] as $item)
            <tr class="table_body">
                <td style="color: #000">{{ $item['combinations']['product']['name'] }}
                    @if (count($item['combinations']['attributeValues']) != 1 || $item['combinations']['attributeValues'][0]['name']!='default')
                        -
                        @foreach($item['combinations']['attributeValues'] as $key=>$val)
                            @if($key!=0 &&
                                $item['combinations']['attributeValues'][$key-1]['name']!='default' &&
                                $val['name']!='default'),@endif
                            {{ $val['name']=='default' ? '' : $val['name'] }}
                        @endforeach
                    @endif
                    </td>
                <td>{{ $item['product_quantity'] }}</td>
                <td>{{ $item['combinations']['selling_price'] }}</td>
                <td>{{ $item['product_quantity'] * $item['combinations']['selling_price'] }}</td>
            </tr>
            @php
            $total += $item['product_quantity'] * $item['combinations']['selling_price'];
            @endphp
        @endforeach
    </table>
    <div style="background-color: #374151; height: 1px; width: 100%;"></div>
    <div style="float: right; margin-right: 27px">
        <p>
            <span style="margin-right: 140px">Subtotal</span> Tk {{ $total }}<br>
            <span style="margin-right: 136px">Discount</span> Tk {{ ($total * $cart['discount_amount'])/100 }} ({{ $cart['discount_amount'] }}%)<br>
            <span style="margin-right: 160px">Total</span> Tk {{ $total - (($total * $cart['discount_amount'])/100) }}<br>
        </p>
    </div>
</section>
<section style="position: absolute; bottom: 0; width: 100%">
    <div style="background-color: brown; height: 1px; width: 100%"></div>
    <p>Email: <span>{{ $general['email'] }}</span><br>Phone: {{ $general['phone'] }}</p>
</section>
</body>
</html>
