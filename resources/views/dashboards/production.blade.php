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

    <!-- Quick Actions -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        @can('create', App\Models\FinishedGood::class)
            <a href="{{ route('finished_goods.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                Record Output
            </a>
        @endcan

        @can('create', App\Models\FinishedGood::class)
            <a href="{{ route('finished_goods.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                Record Finished Goods
            </a>
        @endcan
    </div>

    <!-- Search & Filter -->
    <div class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="table-search" class="form-control" placeholder="Search by JO#, product...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="status-filter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Pending Job Orders -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Pending Job Orders</h5>
            <small class="text-muted">Job orders awaiting or in production</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table pending-jobs-table">
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
                            <tr data-status="{{ $jo->status }}">
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
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Recent Finished Goods</h5>
            <small class="text-muted">Latest production output records</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table finished-goods-table">
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

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('table-search');
                const statusFilter = document.getElementById('status-filter');

                const pendingJobsRows = document.querySelectorAll('.pending-jobs-table tbody tr');
                const finishedGoodsRows = document.querySelectorAll('.finished-goods-table tbody tr');

                function filterTables() {
                    const searchText = (searchInput?.value || '').toLowerCase().trim();
                    const selectedStatus = statusFilter?.value || '';

                    // Filter Pending Job Orders (search + status)
                    pendingJobsRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const status = row.dataset.status || '';
                        const matchesSearch = text.includes(searchText);
                        const matchesStatus = !selectedStatus || status === selectedStatus;
                        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                    });

                    // Filter Recent Finished Goods (search only)
                    finishedGoodsRows.forEach(row => {
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