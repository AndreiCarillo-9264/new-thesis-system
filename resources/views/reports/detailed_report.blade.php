<!-- New: resources/views/reports/detailed_report.blade.php -->
<h1>Detailed Sales Report</h1>
<p>From {{ $start }} to {{ $end }}</p>
<table border="1">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total Amount</th>
            <th>Order Status</th>
            <th>Production Status</th>
            <th>Due Date</th>
            <th>Sales Rep</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $order)
            <tr>
                <td>{{ $order->jo_number }}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ $order->product?->product_name }}</td>
                <td>{{ $order->ordered_quantity }}</td>
                <td>{{ $order->total_amount }}</td>
                <td>{{ $order->status }}</td>
                <td>{{ $order->production_status }}</td>
                <td>{{ $order->due_date }}</td>
                <td>{{ $order->salesRep?->name }}</td>
            </tr>
        @endforeach
    </tbody>
</table>