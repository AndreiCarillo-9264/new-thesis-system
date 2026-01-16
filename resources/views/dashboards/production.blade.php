<!-- Updated: resources/views/dashboards/production.blade.php -->
@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/production.svg') }}" width="32" height="32" alt="Production">
@endsection

@section('page-title', 'Production Dashboard')
@section('page-subtitle', 'Track job orders and record finished goods output')

@section('content')

    <!-- Metrics Cards (unchanged) -->
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

    <!-- Action Buttons -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createWorkOrderModal">
            + Record Output
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#recordFinishedGoodsModal">
            + Record Finished Goods
        </button>
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

    <!-- Pending Job Orders Table -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Pending Job Orders</h5>
            <small>Job orders awaiting or in production</small>
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
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Finished Goods Table -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Recent Finished Goods</h5>
            <small>Latest production output records</small>
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
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create New Work Order Modal -->
    <div class="modal fade" id="createWorkOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Work Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Enter the details for new production work order</p>
                    <form id="createWorkOrderForm">
                        <div class="mb-3">
                            <label class="form-label">Customer*</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Enter customer name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product*</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity*</label>
                            <input type="number" name="ordered_quantity" class="form-control" placeholder="Enter quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority*</label>
                            <select name="priority" class="form-select" required>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date*</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned Team*</label>
                            <select name="assigned_team" class="form-select" required>
                                <option value="Team A">Team A</option>
                                <option value="Team B">Team B</option>
                                <option value="Team C">Team C</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createWorkOrderBtn">Create Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Finished Goods Modal -->
    <div class="modal fade" id="recordFinishedGoodsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Finished Goods</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Enter production completion details</p>
                    <form id="recordFinishedGoodsForm">
                        <div class="mb-3">
                            <label class="form-label">JO Number*</label>
                            <select name="job_order_id" id="jo-number-select" class="form-select" required>
                                <option value="">Select JO Number</option>
                                <!-- Populated by JS or initial load -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product*</label>
                            <input type="text" name="product_name" id="product-name" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Qty Produced*</label>
                            <input type="number" name="quantity_produced" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Production Date*</label>
                            <input type="date" name="production_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="recordOutputBtn">Record Output</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/production.js') }}"></script>
@endsection