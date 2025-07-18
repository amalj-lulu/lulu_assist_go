<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Product</th>
            <th>Serial Number</th>
            <th>Price</th>
            <th>User</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $order)
            @foreach ($order['items'] as $item)
                <tr>
                    <td>{{ $order['order_id'] }}</td>
                    <td>{{ $order['order_date'] }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td>{{ $item['serial_number'] }}</td>
                    <td>₹{{ number_format($item['price'], 2) }}</td>
                    <td>{{ $item['user_name'] }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><strong>Total Orders:</strong> {{ $totalOrders }}</td>
            <td colspan="2"><strong>Total Items:</strong> {{ $totalItems }}</td>
            <td colspan="2"><strong>Total Price:</strong> ₹{{ number_format($totalPrice, 2) }}</td>
        </tr>
    </tfoot>
</table>
