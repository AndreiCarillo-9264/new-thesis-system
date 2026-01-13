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

    <!-- Current Inventory Levels -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Current Inventory Levels</h5>
                <small class="text-muted">Physical stock per product (truth source)</small>
            </div>
            @can('update', App\Models\ActualInventory::class)
                <a href="{{ route('actual_inventories.index') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/edit.svg') }}" width="16" height="16" alt="">
                    Adjust Stock
                </a>
            @endcan
        </div>

        <!-- <div class="section-card-body">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
                <div class="flex-grow-1" style="min-width: 240px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-search.svg') }}" width="18" height="18" alt="Search">
                        </span>
                        <input type="text" id="search-input" class="form-control border-start-0"
                               placeholder="Search by SO number, customer, or product..." aria-label="Search orders">
                    </div>
                </div>
                <div>
                    <select id="status-filter" class="form-select" style="min-width: 160px;">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->name }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div> -->

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table">
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
                            <tr>
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
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Inventory Transfers</h5>
                <small class="text-muted">Internal movements (no change to total stock)</small>
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

    <div class="section-card-body">
            <!-- Search & Filter -->
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
                <div class="flex-grow-1" style="min-width: 240px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-search.svg') }}" width="18" height="18" alt="Search">
                        </span>
                        <input type="text" id="search-input" class="form-control border-start-0"
                               placeholder="Search by product code, name, or warehouse..." aria-label="Search inventory">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <select id="warehouse-filter" class="form-select" style="min-width: 160px;">
                        <option value="">All Warehouses</option>
                        <option value="Not Set">Not Set</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse }}">{{ $warehouse }}</option>
                        @endforeach
                    </select>
                    <select id="stock-status-filter" class="form-select" style="min-width: 160px;">
                        <option value="">All Status</option>
                        <option value="low">Low Stock</option>
                        <option value="normal">In Stock</option>
                        <option value="zero">Out of Stock</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="admin-table w-100 mb-0" id="inventory-table">
                    <thead>
                        <tr>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Warehouse</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th>Last Movement</th>
                            @can('update', App\Models\ActualInventory::class)<th>Actions</th>@endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
                            <tr data-warehouse="{{ $inventory->warehouse ?? 'Not Set' }}">
                                <td><strong>{{ $inventory->product->product_code }}</strong></td>
                                <td>{{ $inventory->product->product_name }}</td>
                                <td>{{ $inventory->product->category ?? '-' }}</td>
                                <td>{{ $inventory->warehouse ?? 'Not Set' }}</td>
                                <td>{{ number_format($inventory->current_stock) }}</td>
                                <td>{{ $inventory->reorder_level }}</td>
                                <td>
                                    @if($inventory->current_stock == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($inventory->current_stock <= $inventory->reorder_level)
                                        <span class="badge bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td>{{ $inventory->latestMovement?->movement_date->format('M d, Y') ?? '-' }}</td>
                                @can('update', App\Models\ActualInventory::class)
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary adjust-btn"
                                                data-id="{{ $inventory->id }}"
                                                data-product="{{ $inventory->product->product_code }} - {{ $inventory->product->product_name }}"
                                                data-stock="{{ $inventory->current_stock }}">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-edit-alt.svg') }}" width="16" height="16">
                                        </button>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="@can('update', App\Models\ActualInventory::class)9@else8@endcan" class="text-center text-muted py-5">
                                    No inventory records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @cannot('update', App\Models\ActualInventory::class)
                <div class="alert alert-info mt-4">
                    <small>ℹ️ You are viewing the Inventory dashboard in <strong>read-only</strong> mode. Only members of the Inventory department can record movements or adjust stock.</small>
                </div>
            @endcannot
        </div>
    </div>

    @can('update', App\Models\ActualInventory::class)
        <!-- Record Movement Modal -->
        <!-- <div id="modal-movement" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Record Inventory Movement</h4>
                    <p class="modal-subtitle">Log stock in, out, or adjustment</p>
                </div>
                <div class="modal-body">
                    <form id="movement-form" action="{{ route('inventory.movements.store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label class="form-label">Product *</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select a product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->product_code }} - {{ $product->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Movement Type *</label>
                                    <select name="movement_type" class="form-select" required>
                                        <option value="IN">Stock In (Receive)</option>
                                        <option value="OUT">Stock Out (Issue)</option>
                                        <option value="ADJUST">Adjustment</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" name="quantity" class="form-control" min="1" step="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Reference Type</label>
                            <select name="reference_type" class="form-select">
                                <option value="">None</option>
                                <option value="SO">Sales Order</option>
                                <option value="PROD">Production</option>
                                <option value="DELIVERY">Delivery</option>
                                <option value="PURCHASE">Purchase</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Reference ID</label>
                            <input type="text" name="reference_id" class="form-control" placeholder="e.g., SO-2025-001">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-movement">Cancel</button>
                            <button type="submit" class="btn btn-primary">Record Movement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- Adjust Stock Modal -->
        <!-- <div id="modal-adjust" class="modal-overlay d-none">
            <div class="modal-panel" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Adjust Stock Level</h4>
                    <p class="modal-subtitle">Manually set current stock quantity</p>
                </div>
                <div class="modal-body">
                    <form id="adjust-form" action="{{ route('inventory.adjust') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="inventory_id" id="adjust-inventory-id">

                        <div class="form-group mb-3">
                            <label class="form-label">Product</label>
                            <p id="adjust-product-name" class="fw-bold mb-0"></p>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Current Stock</label>
                            <p id="adjust-current-stock" class="text-muted mb-0"></p>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">New Stock Quantity *</label>
                            <input type="number" name="new_quantity" class="form-control" min="0" step="1" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Reason *</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="e.g., Physical count correction"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-adjust">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Stock</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->
    @endcan

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/inventory.js') }}"></script>
@endsection