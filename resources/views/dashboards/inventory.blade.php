@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/inventory.svg') }}" width="32" height="32" alt="Inventory">
@endsection

@section('page-title', 'Inventory Dashboard')
@section('page-subtitle', 'Monitor current stock levels and internal transfers')

@section('content')

    <!-- KPI Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Stock On Hand</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($stockOnHand ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center {{ ($lowStockCount ?? 0) > 0 ? 'border-warning border-2' : '' }}">
                <h5 class="text-muted mb-1 fw-medium">Low Stock Items</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($lowStockCount ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Stock In Today</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($stockInToday ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Stock Out Today</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($stockOutToday ?? 0) }}</h2>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        @can('create', App\Models\InventoryTransfer::class)
            <a href="{{ route('inventory_transfers.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                New Transfer
            </a>
        @endcan

        @can('update', App\Models\ActualInventory::class)
            <a href="{{ route('actual_inventories.index') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                Adjust Stock
            </a>
        @endcan
    </div>

    <!-- Search & Filter -->
    <div class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="table-search" class="form-control" placeholder="Search by product code, name...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="status-filter" class="form-select">
                    <option value="">All Status</option>
                    <option value="low">Low Stock</option>
                    <option value="normal">In Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Current Inventory Levels -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Current Inventory Levels</h5>
            <small class="text-muted">Physical stock per product (truth source)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table inventory-table">
                    <thead>
                        <tr>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Actual Quantity</th>
                            <th>Last Counted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories ?? [] as $inv)
                            <tr data-status="{{ $inv->actual_quantity <= 0 ? 'out' : ($inv->actual_quantity < 50 ? 'low' : 'normal') }}">
                                <td class="fw-medium">{{ $inv->product?->product_code ?? '—' }}</td>
                                <td>{{ $inv->product?->product_name ?? '—' }}</td>
                                <td>{{ $inv->product?->category ?? '—' }}</td>
                                <td>{{ $inv->product?->unit ?? '—' }}</td>
                                <td class="fw-bold">{{ number_format($inv->actual_quantity) }}</td>
                                <td>{{ $inv->last_counted_at?->format('M d, Y H:i') ?? '—' }}</td>
                                <td>
                                    @if($inv->actual_quantity <= 0)
                                        <span class="badge bg-danger rounded-pill px-3 py-2">Out of Stock</span>
                                    @elseif($inv->actual_quantity < 50)
                                        <span class="badge bg-warning rounded-pill px-3 py-2">Low Stock</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-3 py-2">In Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No inventory records found</td></tr>
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
            <small class="text-muted">Internal movements (no change to total stock)</small>
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
                                <td>{{ $transfer->product?->product_name ?? '—' }}</td>
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

    @cannot('update', App\Models\ActualInventory::class)
        <div class="alert alert-info mt-4">
            <small>ℹ️ Read-only mode active. Only Inventory department members can adjust stock or record transfers.</small>
        </div>
    @endcannot

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('table-search');
                const statusFilter = document.getElementById('status-filter');

                const inventoryRows = document.querySelectorAll('.inventory-table tbody tr');
                const transfersRows  = document.querySelectorAll('.transfers-table tbody tr');

                function filterTables() {
                    const searchText = (searchInput?.value || '').toLowerCase().trim();
                    const selectedStatus = statusFilter?.value || '';

                    // Filter Inventory Table (search + status)
                    inventoryRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const status = row.dataset.status || '';
                        const matchesSearch = text.includes(searchText);
                        const matchesStatus = !selectedStatus || status === selectedStatus;
                        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                    });

                    // Filter Transfers Table (search only)
                    transfersRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchText) ? '' : 'none';
                    });
                }

                searchInput?.addEventListener('input', filterTables);
                statusFilter?.addEventListener('change', filterTables);
            });
        </script>
    @endsection

@endsection