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

    <!-- Quick Actions -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        @can('create', App\Models\Distribution::class)
            <a href="{{ route('distributions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                New Distribution
            </a>
        @endcan

        @can('create', App\Models\InventoryTransfer::class)
            <a href="{{ route('inventory-transfers.create') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                New Transfer
            </a>
        @endcan
    </div>

    <!-- Search & Filter -->
    <div class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="table-search" class="form-control" placeholder="Search by JO#, product, location...">
                </div>
            </div>
            <!-- No status filter for Logistics – Distributions don't have status in schema -->
        </div>
    </div>

    <!-- Recent Distributions -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Recent Distributions</h5>
            <small class="text-muted">Latest outbound shipments and deliveries</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table distributions-table">
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
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Recent Inventory Transfers</h5>
            <small class="text-muted">Internal movements between locations</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table transfers-table">
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
                        @forelse($transfers ?? [] as $transfer)
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

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('table-search');

                const distributionsRows = document.querySelectorAll('.distributions-table tbody tr');
                const transfersRows     = document.querySelectorAll('.transfers-table tbody tr');

                function filterTables() {
                    const searchText = (searchInput?.value || '').toLowerCase().trim();

                    // Filter Distributions
                    distributionsRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchText) ? '' : 'none';
                    });

                    // Filter Transfers
                    transfersRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchText) ? '' : 'none';
                    });
                }

                if (searchInput) {
                    searchInput.addEventListener('input', filterTables);
                }
            });
        </script>
    @endsection

@endsection