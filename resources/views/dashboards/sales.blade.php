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

            <!-- @if($canEdit)
                <div class="d-flex align-items-center gap-2">
                    <button id="btn-generate-report" class="btn btn-outline-secondary d-flex align-items-center gap-2 px-3">
                        <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-file.svg') }}" width="18" height="18" alt="Report">
                        Generate Report
                    </button>
                    <button id="btn-new-order" class="btn btn-primary d-flex align-items-center gap-2 px-3"
                            style="background-color: var(--color-primary); border-color: var(--color-primary);">
                        <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="18" height="18" alt="New Order">
                        New Order
                    </button>
                </div>
            @endif -->

            @can('create', App\Models\JobOrder::class)
                <a href="{{ route('job_orders.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    New Job Order
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
                <table id="orders-table" class="table table-hover mb-0 admin-table">
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

    @if($canEdit)
        <!-- New Sales Order Modal -->
        <!-- <div id="modal-new-order" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="modal-order-title">
                <div class="modal-header">
                    <div>
                        <h4 id="modal-order-title" class="modal-title">Create New Sales Order</h4>
                        <p class="modal-subtitle">Add customer details and order items</p>
                    </div>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="{{ route('sales.orders.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">SO Number *</label>
                                    <input type="text" name="so_number" class="form-control" placeholder="e.g., SO-2026-001" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Order Date *</label>
                                    <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer *</label>
                            <div class="input-group">
                                <select name="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->customer_code }} - {{ $customer->customer_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-add-customer">
                                    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="16" height="16">
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="delivery_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status_id" class="form-select" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" {{ $status->name == 'Pending' ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>Order Items</h5>
                        <div id="items-container">
                            <div class="row mb-3 item-row">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <select class="form-select" name="product_id[]" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->product_code }} - {{ $product->product_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary btn-quick-add-product">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="16" height="16">
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="quantity[]" placeholder="Qty" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" class="form-control" name="unit_price[]" placeholder="Unit Price" required>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-danger remove-item">×</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-item" class="btn btn-outline-secondary btn-sm mb-3">
                            + Add Another Item
                        </button>

                        <div class="form-group">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-new-order">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background-color: var(--color-primary); border-color: var(--color-primary);">
                                Create Sales Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- Edit Sales Order Modal -->
        <!-- <div id="modal-edit-order" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="modal-edit-order-title">
                <div class="modal-header">
                    <div>
                        <h4 id="modal-edit-order-title" class="modal-title">Edit Sales Order</h4>
                        <p class="modal-subtitle">Update customer details and order items</p>
                    </div>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">SO Number *</label>
                                    <input type="text" name="so_number" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Order Date *</label>
                                    <input type="date" name="order_date" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer *</label>
                            <div class="input-group">
                                <select name="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->customer_code }} - {{ $customer->customer_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-add-customer">
                                    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="16" height="16">
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="delivery_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status_id" class="form-select" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}">
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>Order Items</h5>
                        <div id="items-container"></div>

                        <button type="button" id="add-item" class="btn btn-outline-secondary btn-sm mb-3">
                            + Add Another Item
                        </button>

                        <div class="form-group">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-edit-order">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background-color: var(--color-primary); border-color: var(--color-primary);">
                                Update Sales Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- View Sales Order Modal -->
        <!-- <div id="modal-view-order" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="modal-view-order-title">
                <div class="modal-header">
                    <div>
                        <h4 id="modal-view-order-title" class="modal-title">View Sales Order</h4>
                        <p class="modal-subtitle">Order details and items</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">SO Number</label>
                            <p id="view-so_number" class="fw-bold"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Order Date</label>
                            <p id="view-order_date"></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Customer</label>
                        <p id="view-customer" class="fw-bold"></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Expected Delivery Date</label>
                            <p id="view-delivery_date"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <p id="view-status" class="badge bg-primary"></p>
                        </div>
                    </div>

                    <hr>

                    <h5>Order Items</h5>
                    <table class="table table-sm" id="view-items-table">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <p id="view-remarks"></p>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-light" data-close-modal="modal-view-order">Close</button>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Quick Add Customer Modal -->
        <!-- <div id="modal-quick-add-customer" class="modal-overlay d-none">
            <div class="modal-panel" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Quick Add Customer</h4>
                </div>
                <div class="modal-body">
                    <form id="quick-add-customer-form" action="javascript:void(0)">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Customer Code *</label>
                            <input type="text" name="customer_code" class="form-control" required placeholder="e.g., CUST-001">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-quick-add-customer">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- Quick Add Product Modal -->
        <!-- <div id="modal-quick-add-product" class="modal-overlay d-none">
            <div class="modal-panel" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Quick Add Product</h4>
                </div>
                <div class="modal-body">
                    <form id="quick-add-product-form" action="javascript:void(0)">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Product Code *</label>
                            <input type="text" name="product_code" class="form-control" required placeholder="e.g., PROD-001">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="product_name" class="form-control" required>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-quick-add-product">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->
    @endif

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/sales.js') }}"></script>
@endsection