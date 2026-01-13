@extends('layouts.app')

@section('title', 'Main Dashboard')

@section('page-icon')
    <img src="{{ asset('assets/icons/dashboard.svg') }}" width="32" height="32" alt="">
@endsection

@section('page-title', 'Main Dashboard')
@section('page-subtitle', 'Real-time overview of operations and key performance indicators')

@section('content')

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        @foreach([
            ['label' => 'Total Job Orders',     'value' => $totalJobOrders     ?? 0],
            ['label' => 'Total Finished Goods', 'value' => $totalProduced      ?? 0],
            ['label' => 'Total Distributed',    'value' => $totalDistributed   ?? 0],
            ['label' => 'Current Inventory',    'value' => $currentInventory   ?? 0],
        ] as $kpi)
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card text-center">
                    <h5 class="text-muted mb-1 fw-medium">{{ $kpi['label'] }}</h5>
                    <h2 class="mb-0 fw-bold text-primary">{{ number_format($kpi['value']) }}</h2>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-semibold">Ordered vs Produced vs Distributed</h5>
                </div>
                <div class="card-body p-3" style="height: 380px;">
                    <canvas id="comparisonChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-semibold">Inventory Trend (Last 12 Months)</h5>
                </div>
                <div class="card-body p-3" style="height: 380px;">
                    <canvas id="inventoryTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-4">
        <!-- Recent Job Orders -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-semibold">Recent Job Orders</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 admin-table">
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
                                        <td class="fw-medium">{{ $jo->jo_number }}</td>
                                        <td>{{ $jo->product?->product_name ?? '—' }}</td>
                                        <td>{{ number_format($jo->ordered_quantity) }}</td>
                                        <td>{{ $jo->jo_date?->format('M d, Y') ?? '—' }}</td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2 bg-{{ match($jo->status) {
                                                'open'       => 'warning',
                                                'in_progress'=> 'info',
                                                'completed'  => 'success',
                                                'cancelled'  => 'danger',
                                                default      => 'secondary'
                                            } }}">
                                                {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No recent job orders found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Finished Goods -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-semibold">Recent Finished Goods</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 admin-table">
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
                                        <td class="fw-medium">{{ $fg->product?->product_name ?? '—' }}</td>
                                        <td>{{ number_format($fg->quantity_produced) }}</td>
                                        <td>{{ $fg->production_date?->format('M d, Y') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted">No recent finished goods</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/main.js') }}"></script>
@endsection