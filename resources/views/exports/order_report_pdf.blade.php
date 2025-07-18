<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .summary {
            margin-bottom: 20px;
        }

        .summary p {
            margin: 0;
            padding: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th,
        td {
            border: 1px solid #888;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .sub-table th,
        .sub-table td {
            font-size: 11px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <h2>Order Report</h2>

    <div class="summary">
        <p><strong>Total Orders:</strong> {{ $report['total_orders'] }}</p>
        <p><strong>Total Items:</strong> {{ $report['total_items'] }}</p>
        <p><strong>Total Price:</strong> ₹{{ number_format($report['total_price'], 2) }}</p>
    </div>

    @foreach ($report['orders'] as $order)
        <div style="page-break-inside: avoid;">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Item Count</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $order['order_id'] }}</td>
                        <td>{{ $order['order_date'] }}</td>
                        <td>{{ $order['item_count'] }}</td>
                        <td>₹{{ number_format($order['total_price'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="sub-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Serial Number</th>
                        <th>Price</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order['items'] as $item)
                        <tr>
                            <td>{{ $item['product_name'] }}</td>
                            <td>{{ $item['serial_number'] }}</td>
                            <td>₹{{ number_format($item['price'], 2) }}</td>
                            <td>{{ $item['user_name'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>

</html>
