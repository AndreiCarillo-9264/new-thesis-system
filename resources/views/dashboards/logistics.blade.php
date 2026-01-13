@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/logistics.svg') }}" width="32" height="32" alt="Logistics">
@endsection

@section('page-title', 'Logistics Dashboard')
@section('page-subtitle', 'Track distributions and internal inventory transfers')

@section('content')

    <!-- KPI Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Deliveries Today</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($deliveriesToday ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Pending Dispatch</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($pendingDispatch ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Transfers Today</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($transfersToday ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Delayed Shipments</h5>
                <h2 class="mb-0 fw-bold text-danger">{{ number_format($delayedShipments ?? 0) }}</h2>
            </div>
        </div>
    </div>

    <!-- Recent Distributions -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Distributions</h5>
                <small class="text-muted">Latest outbound shipments and deliveries</small>
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

    <!-- Recent Inventory Transfers -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Inventory Transfers</h5>
                <small class="text-muted">Internal movements between locations</small>
            </div>
            @can('create', App\Models\InventoryTransfer::class)
                <a href="{{ route('inventory_transfers.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    New Transfer
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>From Location</th>
                            <th>To Location</th>
                            <th>Transfer Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransfers ?? [] as $transfer)
                            <tr>
                                <td class="fw-medium">{{ $transfer->product?->product_name ?? '—' }}</td>
                                <td>{{ number_format($transfer->quantity) }}</td>
                                <td>{{ $transfer->from_location ?? '—' }}</td>
                                <td>{{ $transfer->to_location ?? '—' }}</td>
                                <td>{{ $transfer->transfer_date?->format('M d, Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No recent transfers</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @cannot('create', App\Models\Distribution::class)
        <div class="alert alert-info mt-4">
            <small>ℹ️ You are viewing in read-only mode. Only Logistics department members can create or modify distributions and transfers.</small>
        </div>
    @endcannot

@endsection