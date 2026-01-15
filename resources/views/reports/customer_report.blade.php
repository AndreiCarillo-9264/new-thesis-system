<!-- New: resources/views/reports/customer_report.blade.php -->
<h1>Customer Sales Report</h1>
<p>From {{ $start }} to {{ $end }}</p>
<table border="1">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Orders Count</th>
            <th>Total Spent</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
            <tr>
                <td>{{ $item->customer_name }}</td>
                <td>{{ $item->orders_count }}</td>
                <td>{{ $item->total_spent }}</td>
            </tr>
        @endforeach
    </tbody>
</table>