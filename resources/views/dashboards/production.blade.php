@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/production.svg') }}" width="32" height="32" alt="Production">
@endsection

@section('page-title', 'Production Dashboard')
@section('page-subtitle', 'Track job orders and record finished goods output')

@section('content')

    <!-- KPI Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Pending Production</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($pendingProduction ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Produced Today</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($producedToday ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Completion %</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ $completionPercentage ?? 0 }}%</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Backlog Quantity</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($backlog ?? 0) }}</h2>
            </div>
        </div>
    </div>

    <!-- Recent Job Orders (Pending/In Progress) -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Pending Job Orders</h5>
                <small class="text-muted">Job orders awaiting or in production</small>
            </div>
            @can('create', App\Models\FinishedGood::class)
                <a href="{{ route('finished_goods.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    Record Finished Goods
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
                            <tr><td colspan="5" class="text-center py-5 text-muted">No pending job orders</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Finished Goods -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Finished Goods</h5>
                <small class="text-muted">Latest production output records</small>
            </div>
            @can('create', App\Models\FinishedGood::class)
                <a href="{{ route('finished_goods.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    Record Output
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
                            <th>Qty Produced</th>
                            <th>Production Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentFG ?? [] as $fg)
                            <tr>
                                <td class="fw-medium">{{ $fg->jobOrder?->jo_number ?? '—' }}</td>
                                <td>{{ $fg->product?->product_name ?? '—' }}</td>
                                <td>{{ number_format($fg->quantity_produced) }}</td>
                                <td>{{ $fg->production_date?->format('M d, Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">No recent production output</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @cannot('create', App\Models\FinishedGood::class)
        <div class="alert alert-info mt-4">
            <small>ℹ️ You are viewing in read-only mode. Only Production department members can record finished goods output.</small>
        </div>
    @endcannot

@endsection