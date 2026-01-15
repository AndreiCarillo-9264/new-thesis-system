<h1>Sales Summary Report</h1>
<p>From {{ $start }} to {{ $end }}</p>
<table border="1">
    <thead>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $value)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>