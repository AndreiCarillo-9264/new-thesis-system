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

        <div class="section-card-body">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
                <div class="flex-grow-1" style="min-width: 240px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-search.svg') }}" width="18" height="18" alt="Search">
                        </span>
                        <input type="text" id="search-input" class="form-control border-start-0"
                               placeholder="Search by JO number, product..." aria-label="Search job orders">
                    </div>
                </div>
                <div>
                    <select id="status-filter" class="form-select" style="min-width: 160px;">
                        <option value="">All Statuses</option>
                        @if(isset($statuses))
                            @foreach($statuses as $status)
                                <option value="{{ $status->name }}">{{ $status->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="production-table" class="table table-hover mb-0 admin-table">
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

    @can('create', App\Models\FinishedGood::class)
        <!-- Work Order Modal (Create/Edit) -->
        <!-- <div id="modal-work-order" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-work-title">Create New Production Order</h4>
                    <p class="modal-subtitle">Link to a Sales Order and assign production details</p>
                </div>
                <div class="modal-body">
                    <form id="work-order-form">
                        @csrf
                        <input type="hidden" name="id" id="work-order-id">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Production Code *</label>
                                <input type="text" name="production_code" id="production_code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department *</label>
                                <select name="department" id="department" class="form-select" required>
                                    <option value="DS">DS</option>
                                    <option value="FG">FG</option>
                                    <option value="Assembly">Assembly</option>
                                    <option value="Packaging">Packaging</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Linked Sales Order *</label>
                            <select name="sales_order_id" id="sales_order_id" class="form-select" required>
                                <option value="">Select a Sales Order</option>
                                @foreach($eligibleSalesOrders as $so)
                                    <option value="{{ $so->id }}">
                                        {{ $so->so_number }} - {{ $so->customer->customer_name }} ({{ $so->items->sum('quantity') }} units)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Planned Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Planned End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status_id" id="status_id" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-work-order">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Work Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- Record Output Modal -->
        <!-- <div id="modal-output" class="modal-overlay d-none">
            <div class="modal-panel" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Record Production Output</h4>
                    <p class="modal-subtitle">Log completed units for this work order</p>
                </div>
                <div class="modal-body">
                    <form id="output-form">
                        @csrf
                        <input type="hidden" name="production_order_id" id="output-production-id">

                        <div class="form-group mb-3">
                            <label class="form-label">Work Order</label>
                            <p id="output-work-code" class="fw-bold mb-0"></p>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Product</label>
                            <p id="output-product" class="text-muted mb-0"></p>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Quantity Produced *</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="output_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-output">Cancel</button>
                            <button type="submit" class="btn btn-primary">Record Output</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- View Modal -->
        <!-- <div id="modal-view" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Production Order Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><strong>Production Code:</strong> <span id="view-code"></span></div>
                        <div class="col-md-6"><strong>Department:</strong> <span id="view-dept"></span></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6"><strong>Linked SO:</strong> <span id="view-so"></span></div>
                        <div class="col-md-6"><strong>Customer:</strong> <span id="view-customer"></span></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6"><strong>Start Date:</strong> <span id="view-start"></span></div>
                        <div class="col-md-6"><strong>End Date:</strong> <span id="view-end"></span></div>
                    </div>
                    <hr>
                    <div><strong>Status:</strong> <span id="view-status" class="badge bg-primary"></span></div>
                    <div class="mt-3"><strong>Progress:</strong>
                        <div class="progress mt-2">
                            <div id="view-progress" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                        </div>
                    </div>
                    <div class="mt-3"><strong>Remarks:</strong> <p id="view-remarks" class="mt-2"></p></div>

                    <div class="modal-actions mt-4">
                        <button type="button" class="btn btn-light" data-close-modal="modal-view">Close</button>
                    </div>
                </div>
            </div>
        </div> -->
    @endcan

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/production.js') }}"></script>
@endsection