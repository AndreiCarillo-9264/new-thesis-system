@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/sales.svg') }}" width="32" height="32" alt="Sales">
@endsection

@section('page-title', 'Sales Dashboard')
@section('page-subtitle', 'Manage job orders and track distributions')

@section('content')

    <!-- KPI Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Total Job Orders</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($totalJobOrders ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Open Job Orders</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($openJobOrders ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Delivered This Month</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($deliveredThisMonth ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Pending Deliveries</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($pendingDeliveries ?? 0) }}</h2>
            </div>
        </div>
    </div>

    <!-- Recent Job Orders -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Job Orders</h5>
                <small class="text-muted">Latest created/updated job orders</small>
            </div>
            @can('create', App\Models\JobOrder::class)
                <a href="{{ route('job_orders.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    New Job Order
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>JO Number</th>
                            <th>Product</th>
                            <th>Ordered Qty</th>
                            <th>JO Date</th>
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
                                        'open'        => 'warning',
                                        'in_progress' => 'info',
                                        'completed'   => 'success',
                                        'cancelled'   => 'danger',
                                        default       => 'secondary'
                                    } }}">
                                        {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No recent job orders</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Distributions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Distributions</h5>
                <small class="text-muted">Latest outbound shipments / deliveries</small>
            </div>
            @can('create', App\Models\Distribution::class)
                <a href="{{ route('distributions.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    New Distribution
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>JO Number</th>
                            <th>Product</th>
                            <th>Qty Distributed</th>
                            <th>Date</th>
                            <th>Destination</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDistributions ?? [] as $dist)
                            <tr>
                                <td class="fw-medium">{{ $dist->jobOrder?->jo_number ?? '—' }}</td>
                                <td>{{ $dist->product?->product_name ?? '—' }}</td>
                                <td>{{ number_format($dist->quantity_distributed) }}</td>
                                <td>{{ $dist->distribution_date?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $dist->destination ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No recent distributions</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @cannot('create', App\Models\JobOrder::class)
        <div class="alert alert-info mt-4">
            <small>ℹ️ You are viewing in read-only mode. Only Sales department members can create or modify job orders and distributions.</small>
        </div>
    @endcannot

@endsection