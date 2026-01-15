@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/sales.svg') }}" width="32" height="32" alt="Sales">
@endsection

@section('page-title', 'Sales Management')
@section('page-subtitle', 'Track customer orders and coordinate with production')

@section('content')

    <!-- Metrics Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Revenue</h5>
                <h2 class="mb-0 fw-bold text-primary">${{ number_format($revenue) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Pending Orders</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ $pendingOrders }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Completed Orders</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ $completedOrders }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">On-Time Delivery</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ $onTimePercentage }}%</h2>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        <button class="btn btn-outline-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#generateReportModal">
            <i class="bi bi-file-earmark-bar-graph"></i> Generate Report
        </button>
        @can('create', App\Models\JobOrder::class)
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                <i class="bi bi-plus-circle"></i> + New Order
            </button>
        @endcan
    </div>

    <!-- Search & Filter -->
    <div class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="table-search" class="form-control" placeholder="Search orders...">
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

    <!-- Sales Orders Table (Pending) -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Sales Orders</h5>
            <small class="text-muted">Customer orders awaiting production or fulfillment</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table sales-orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Order Status</th>
                            <th>Production Status</th>
                            <th>Due Date</th>
                            <th>Sales Rep</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Completed Orders Table (Distributions) -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Recent Completed Orders</h5>
            <small class="text-muted">Latest fulfilled and distributed orders</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table completed-orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity Delivered</th>
                            <th>Completion Date</th>
                            <th>Delivery Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JS -->
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

    <!-- Generate Report Modal -->
    <div class="modal fade" id="generateReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Sales Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Configure report parameters and generate sales analytics</p>
                    <form id="generateReportForm">
                        <div class="mb-3">
                            <label class="form-label">Report Type*</label>
                            <select name="report_type" class="form-select" required>
                                <option value="">Select</option>
                                <option value="Sales Summary">Sales Summary</option>
                                <option value="Detailed Report">Detailed Report</option>
                                <option value="Customer Report">Customer Report</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Date*</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date*</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format*</label>
                            <select name="format" class="form-select" required>
                                <option value="">Select</option>
                                <option value="PDF">PDF</option>
                                <option value="Excel">Excel</option>
                                <option value="CSV">CSV</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="generateReportBtn">Generate Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create New Sales Order Modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Sales Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Enter the details for the new sales order</p>
                    <form id="createOrderForm">
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
                            <input type="number" name="ordered_quantity" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit Price*</label>
                            <input type="number" name="unit_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="text" id="total_amount" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date*</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sales Representative*</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select sales rep</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority*</label>
                            <select name="priority" class="form-select" required>
                                <option value="high">High</option>
                                <option value="medium" selected>Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createOrderBtn">Create Order</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/sales.js') }}"></script>
@endsection
</DOCUMENT>