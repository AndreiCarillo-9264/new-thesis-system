@extends('layouts.app')

@section('title', 'Main Dashboard')

@section('page-icon')
    <i class="bx bx-grid-alt bx-lg" style="color: var(--color-primary);"></i>
@endsection

@section('page-title', 'Main Dashboard')
@section('page-subtitle', 'Real-time overview of operations and key performance indicators')

@section('content')
    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="stats-card h-100">
            <h5 class="mb-1 text-muted">Total Job Orders</h5>
            <h2 class="mb-0">{{ number_format($totalJobOrders ?? 0) }}</h2>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="stats-card h-100">
            <h5 class="mb-1 text-muted">Total Finished Goods</h5>
            <h2 class="mb-0">{{ number_format($totalProduced ?? 0) }}</h2>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="stats-card h-100">
            <h5 class="mb-1 text-muted">Total Distributed</h5>
            <h2 class="mb-0">{{ number_format($totalDistributed ?? 0) }}</h2>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="stats-card h-100">
            <h5 class="mb-1 text-muted">Current Inventory</h5>
            <h2 class="mb-0">{{ number_format($currentInventory ?? 0) }}</h2>
        </div>
    </div>
</div>

    <!-- Charts -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-card-header">
                    <h3>Ordered vs Produced vs Distributed</h3>
                </div>
                <div class="section-card-body">
                    <canvas id="comparisonChart" height="180"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-card-header">
                    <h3>Inventory Trend (Last 12 Months)</h3>
                </div>
                <div class="section-card-body">
                    <canvas id="inventoryTrendChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-4">
        <!-- Recent Job Orders -->
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-card-header">
                    <h3>Recent Job Orders</h3>
                </div>
                <div class="section-card-body p-0">
                    <table class="admin-table w-100">
                        <thead>
                            <tr>
                                <th>JO #</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentJobOrders ?? [] as $jo)
                                <tr>
                                    <td>{{ $jo->jo_number }}</td>
                                    <td>{{ $jo->product?->product_name ?? '—' }}</td>
                                    <td>{{ number_format($jo->ordered_quantity) }}</td>
                                    <td>{{ $jo->jo_date?->format('M d, Y') ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ match($jo->status) {
                                            'open' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        } }}">
                                            {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No recent job orders</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Finished Goods -->
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-card-header">
                    <h3>Recent Finished Goods</h3>
                </div>
                <div class="section-card-body p-0">
                    <table class="admin-table w-100">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty Produced</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFG ?? [] as $fg)
                                <tr>
                                    <td>{{ $fg->product?->product_name ?? '—' }}</td>
                                    <td>{{ number_format($fg->quantity_produced) }}</td>
                                    <td>{{ $fg->production_date?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">No recent records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- You can add the next two tables (Distributions & Low Stock) in the same pattern -->
    </div>
@endsection

@section('scripts')
<script>
    // Comparison Chart
    new Chart(document.getElementById('comparisonChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                { label: 'Ordered',     data: [1200,1900,3000,2500,2800,3200,4000,3800,3500,3000,2700,2400], backgroundColor: 'rgba(165,107,85,0.65)', borderColor: '#A56B55', borderWidth: 1 },
                { label: 'Produced',    data: [1100,1700,2800,2300,2600,3000,3700,3500,3200,2800,2500,2100], backgroundColor: 'rgba(99,54,39,0.65)',   borderColor: '#633627', borderWidth: 1 },
                { label: 'Distributed', data: [900,1400,2400,2000,2200,2600,3200,3000,2700,2400,2100,1800], backgroundColor: 'rgba(217,184,169,0.65)', borderColor: '#D9B8A9', borderWidth: 1 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'top' } }
        }
    });

    // Inventory Trend Chart
    new Chart(document.getElementById('inventoryTrendChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Inventory Level',
                data: [8500,8200,7800,7600,7400,7100,6900,7200,7500,7800,8100,7900],
                borderColor: 'var(--color-primary)',
                backgroundColor: 'rgba(165,107,85,0.18)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: false } }
        }
    });
</script>
@endsection