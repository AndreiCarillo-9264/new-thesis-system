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

        <div class="section-card-body">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
                <div class="flex-grow-1" style="min-width: 240px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-search.svg') }}" width="18" height="18" alt="Search">
                        </span>
                        <input type="text" id="search-input" class="form-control border-start-0"
                               placeholder="Search by JO number, product, or destination..." aria-label="Search distributions">
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
                <table id="deliveries-table" class="table table-hover mb-0 admin-table">
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

    @if($canEdit)
        <!-- Delivery Modal (Create/Edit) -->
        <!-- <div id="modal-delivery" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-delivery-title">Schedule New Delivery</h4>
                    <p class="modal-subtitle">Link a Sales Order and assign logistics details</p>
                </div>
                <div class="modal-body">
                    <form id="delivery-form">
                        @csrf
                        <input type="hidden" name="id" id="delivery_id">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Delivery Number *</label>
                                <input type="text" name="delivery_number" id="delivery_number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivery Date *</label>
                                <input type="date" name="delivery_date" id="delivery_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Sales Order *</label>
                            <select name="sales_order_id" id="sales_order_id" class="form-select" required>
                                <option value="">Select a Sales Order</option>
                                @foreach($eligibleSalesOrders as $so)
                                    <option value="{{ $so->id }}">
                                        {{ $so->so_number }} - {{ $so->customer->customer_name }} ({{ $so->order_date->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Driver</label>
                                <input type="text" name="driver" id="driver" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vehicle</label>
                                <input type="text" name="vehicle" id="vehicle" class="form-control" placeholder="e.g., Truck-01 (ABC-123)">
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

                        <hr>

                        <h5>Items to Deliver (from selected SO)</h5>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Ordered Qty</th>
                                        <th>Qty to Deliver</th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                </tbody>
                            </table>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-delivery">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Delivery</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- View Delivery Modal -->
        <!-- <div id="modal-view-delivery" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Delivery Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Delivery No:</strong> <span id="view-delivery-no"></span></div>
                        <div class="col-md-6"><strong>SO Number:</strong> <span id="view-so-no"></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Customer:</strong> <span id="view-customer"></span></div>
                        <div class="col-md-6"><strong>Delivery Date:</strong> <span id="view-date"></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Driver:</strong> <span id="view-driver"></span></div>
                        <div class="col-md-6"><strong>Vehicle:</strong> <span id="view-vehicle"></span></div>
                    </div>
                    <div class="mb-3"><strong>Status:</strong> <span id="view-status" class="badge bg-primary"></span></div>

                    <hr>

                    <h5>Delivered Items</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="view-items-tbody"></tbody>
                    </table>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-light" data-close-modal="modal-view-delivery">Close</button>
                    </div>
                </div>
            </div>
        </div> -->
    @endif

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/logistics.js') }}"></script>
@endsection