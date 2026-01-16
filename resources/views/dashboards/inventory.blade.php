<!-- Updated: resources/views/dashboards/inventory.blade.php -->
@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/inventory.svg') }}" width="32" height="32" alt="Inventory">
@endsection

@section('page-title', 'Inventory Dashboard')
@section('page-subtitle', 'Monitor current stock levels and internal transfers')

@section('content')

    <!-- Metrics Cards (unchanged) -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Stock On Hand</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($stockOnHand ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center {{ $lowStockCount > 0 ? 'border-warning border-2' : '' }}">
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

    <!-- Action Buttons -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newTransferModal">
            + New Transfer
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
            + Adjust Stock
        </button>
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
                    <option value="adequate">Adequate</option>
                    <option value="overstocked">Overstocked</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Current Inventory Levels Table -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Current Inventory Levels</h5>
            <small>Physical stock per product (truth source)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table inventory-levels-table">
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
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Inventory Transfers Table -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Recent Inventory Transfers</h5>
            <small>Internal movements (no change to total stock)</small>
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
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- New Transfer Modal -->
    <div class="modal fade" id="newTransferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Inventory Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Move inventory between locations (no change to total stock)</p>
                    <form id="newTransferForm">
                        <div class="mb-3">
                            <label class="form-label">Product*</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select product</option>
                                @foreach($inventories as $inv)
                                    <option value="{{ $inv->product_id }}">{{ $inv->product->product_name ?? 'Unknown' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity*</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">From Location*</label>
                            <input type="text" name="from_location" class="form-control" placeholder="e.g., Warehouse A-1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To Location*</label>
                            <input type="text" name="to_location" class="form-control" placeholder="e.g., Warehouse B-2" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transfer Date*</label>
                            <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" placeholder="Add transfer notes (optional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="recordTransferBtn">Record Transfer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Inventory Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Add or remove inventory from total stock</p>
                    <form id="adjustStockForm">
                        <div class="mb-3">
                            <label class="form-label">Product Name*</label>
                            <select name="product_id" id="adjust-product-select" class="form-select" required>
                                <option value="">Select product</option>
                                @foreach($inventories as $inv)
                                    <option value="{{ $inv->product_id }}" data-current="{{ $inv->actual_quantity }}">{{ $inv->product->product_name ?? 'Unknown' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adjustment Type*</label>
                            <select name="adjustment_type" class="form-select" required>
                                <option value="add">Add Stock</option>
                                <option value="remove">Remove Stock</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity*</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Actual Quantity After Adjustment</label>
                            <input type="text" id="new-quantity" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason*</label>
                            <select name="reason" class="form-select" required>
                                <option value="Received Shipment">Received Shipment</option>
                                <option value="Damaged Goods">Damaged Goods</option>
                                <option value="Stock Count Adjustment">Stock Count Adjustment</option>
                                <option value="Return">Return</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" placeholder="Add adjustment notes"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="adjustStockBtn">Adjust Stock</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/inventory.js') }}"></script>
@endsection