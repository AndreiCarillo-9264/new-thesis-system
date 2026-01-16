<!-- Updated: resources/views/dashboards/logistics.blade.php -->
@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/logistics.svg') }}" width="32" height="32" alt="Logistics">
@endsection

@section('page-title', 'Logistics Dashboard')
@section('page-subtitle', 'Track distributions and internal inventory transfers')

@section('content')

    <!-- Metrics Cards (unchanged) -->
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

    <!-- Action Buttons -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newDistributionModal">
            + New Distribution
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newTransferModal">
            + New Transfer
        </button>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="table-search" class="form-control" placeholder="Search by JO#, product, location...">
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Distributions Table -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Recent Distributions</h5>
            <small>Latest outbound shipments and deliveries</small>
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
            <small>Internal movements between locations</small>
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

    <!-- New Distribution Modal -->
    <div class="modal fade" id="newDistributionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record New Distribution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Enter details for outbound shipment/delivery</p>
                    <form id="newDistributionForm">
                        <div class="mb-3">
                            <label class="form-label">JO Number*</label>
                            <select name="job_order_id" id="jo-select" class="form-select" required>
                                <option value="">Select JO Number</option>
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product*</label>
                            <input type="text" name="product_name" id="product-name" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Qty Distributed*</label>
                            <input type="number" name="quantity_distributed" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date*</label>
                            <input type="date" name="distribution_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Destination*</label>
                            <textarea name="destination" class="form-control" placeholder="Enter complete delivery address" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Customer name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Driver</label>
                            <input type="text" name="driver" class="form-control" placeholder="Enter driver name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vehicle</label>
                            <input type="text" name="vehicle" class="form-control" placeholder="e.g., Truck-01 (ABC-123)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" placeholder="Add delivery notes (optional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="recordDistributionBtn">Record Distribution</button>
                </div>
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
                    <p>Move inventory between locations (internal transfer)</p>
                    <form id="newTransferForm">
                        <div class="mb-3">
                            <label class="form-label">Product*</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select product</option>
                                <!-- Populated by JS -->
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
                            <label class="form-label">Reason</label>
                            <select name="reason" class="form-select">
                                <option value="">Select reason</option>
                                <option value="Restock">Restock</option>
                                <option value="Production Requirement">Production Requirement</option>
                                <option value="Optimization">Optimization</option>
                                <option value="Other">Other</option>
                            </select>
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

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/logistics.js') }}"></script>
@endsection