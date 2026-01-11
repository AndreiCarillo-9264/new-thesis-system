@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-wrench.svg') }}" width="48" height="48" alt="Production">
@endsection

@section('page-title', 'Production Dashboard')
@section('page-subtitle', 'Monitor manufacturing work orders and track production output')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Active Work Orders</h5>
                <h2>{{ $stats['active_orders'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Completed Today</h5>
                <h2>{{ $stats['completed_today'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card {{ $stats['behind_schedule'] > 0 ? 'bg-warning-subtle' : '' }}">
                <h5>Behind Schedule</h5>
                <h2>{{ $stats['behind_schedule'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Overall Efficiency</h5>
                <h2>{{ $stats['efficiency'] }}%</h2>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-card-header d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3>Production Work Orders</h3>
                <p>Track and manage production orders from Sales Orders</p>
            </div>

            @if($canEdit)
                <button id="btn-new-work-order" class="btn btn-primary d-flex align-items-center gap-2 px-3"
                        style="background-color: var(--color-primary); border-color: var(--color-primary);">
                    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="18" height="18" alt="New">
                    New Work Order
                </button>
            @endif
        </div>

        <div class="section-card-body">
            <!-- Search & Filter -->
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
                <div class="flex-grow-1" style="min-width: 240px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-search.svg') }}" width="18" height="18" alt="Search">
                        </span>
                        <input type="text" id="search-input" class="form-control border-start-0"
                               placeholder="Search by production code, SO number, customer..." aria-label="Search work orders">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <select id="status-filter" class="form-select" style="min-width: 160px;">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->name }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <select id="department-filter" class="form-select" style="min-width: 160px;">
                        <option value="">All Departments</option>
                        <option value="DS">DS</option>
                        <option value="FG">FG</option>
                        <option value="Assembly">Assembly</option>
                        <option value="Packaging">Packaging</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="admin-table w-100 mb-0" id="production-table">
                    <thead>
                        <tr>
                            <th>Production Code</th>
                            <th>SO Number</th>
                            <th>Customer</th>
                            <th>Department</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Progress</th>
                            <th>Status</th>
                            @if($canEdit)<th>Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productionOrders as $order)
                            <tr>
                                <td><strong>{{ $order->production_code }}</strong></td>
                                <td>{{ $order->salesOrder?->so_number ?? '-' }}</td>
                                <td>{{ $order->salesOrder?->customer?->customer_name ?? '-' }}</td>
                                <td>{{ $order->department ?? '-' }}</td>
                                <td>{{ $order->start_date?->format('M d, Y') ?? '-' }}</td>
                                <td>{{ $order->end_date?->format('M d, Y') ?? '-' }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $order->progress_percentage >= 100 ? 'bg-success' : 'bg-primary' }}"
                                             role="progressbar"
                                             style="width: {{ min($order->progress_percentage, 100) }}%">
                                            {{ round($order->progress_percentage) }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $order->status->name == 'Completed' ? 'success' : 
                                        ($order->status->name == 'In Progress' ? 'primary' : 'secondary') 
                                    }}">
                                        {{ $order->status->name }}
                                    </span>
                                </td>
                                @if($canEdit)
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1 view-btn" data-id="{{ $order->id }}">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-show.svg') }}" width="16" height="16">
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary edit-btn" data-id="{{ $order->id }}">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-edit.svg') }}" width="16" height="16">
                                        </button>
                                        <button class="btn btn-sm btn-outline-info output-btn" data-id="{{ $order->id }}">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-package.svg') }}" width="16" height="16" alt="Record Output">
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canEdit ? '9' : '8' }}" class="text-center text-muted py-5">
                                    No production orders yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!$canEdit)
                <div class="alert alert-info mt-4">
                    <small>ℹ️ You are viewing the Production dashboard in <strong>read-only</strong> mode. Only members of the Production department can create or update work orders.</small>
                </div>
            @endif
        </div>
    </div>

    @if($canEdit)
        <!-- Work Order Modal (Create/Edit) -->
        <div id="modal-work-order" class="modal-overlay d-none">
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
        </div>

        <!-- Record Output Modal -->
        <div id="modal-output" class="modal-overlay d-none">
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
        </div>

        <!-- View Modal -->
        <div id="modal-view" class="modal-overlay d-none">
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
        </div>
    @endif

    <script>
        // Open New Work Order
        document.getElementById('btn-new-work-order')?.addEventListener('click', () => {
            document.getElementById('modal-work-title').textContent = 'Create New Production Order';
            document.getElementById('work-order-form').reset();
            document.getElementById('work-order-id').value = '';
            document.getElementById('modal-work-order').classList.remove('d-none');
        });

        // View / Edit / Output Buttons
        document.querySelectorAll('.view-btn, .edit-btn, .output-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const action = btn.classList.contains('view-btn') ? 'view' :
                              btn.classList.contains('edit-btn') ? 'edit' : 'output';

                const url = '{{ route("production.orders.show", ":id") }}'.replace(':id', id);
                const response = await fetch(url);
                const order = await response.json();

                if (action === 'view') {
                    document.getElementById('view-code').textContent = order.production_code;
                    document.getElementById('view-dept').textContent = order.department;
                    document.getElementById('view-so').textContent = order.salesOrder?.so_number || '-';
                    document.getElementById('view-customer').textContent = order.salesOrder?.customer?.customer_name || '-';
                    document.getElementById('view-start').textContent = order.start_date || '-';
                    document.getElementById('view-end').textContent = order.end_date || '-';
                    document.getElementById('view-status').textContent = order.status.name;
                    document.getElementById('view-status').className = `badge bg-${order.status.name === 'Completed' ? 'success' : 'primary'}`;
                    document.getElementById('view-progress').style.width = `${Math.min(order.progress_percentage, 100)}%`;
                    document.getElementById('view-progress').textContent = `${Math.round(order.progress_percentage)}%`;
                    document.getElementById('view-remarks').textContent = order.remarks || '-';

                    document.getElementById('modal-view').classList.remove('d-none');

                } else if (action === 'edit') {
                    document.getElementById('modal-work-title').textContent = 'Edit Production Order';
                    document.getElementById('work-order-id').value = order.id;
                    document.getElementById('production_code').value = order.production_code;
                    document.getElementById('department').value = order.department;
                    document.getElementById('sales_order_id').value = order.sales_order_id;
                    document.getElementById('start_date').value = order.start_date || '';
                    document.getElementById('end_date').value = order.end_date || '';
                    document.getElementById('status_id').value = order.status_id;
                    document.getElementById('remarks').value = order.remarks || '';

                    document.getElementById('modal-work-order').classList.remove('d-none');

                } else if (action === 'output') {
                    document.getElementById('output-production-id').value = order.id;
                    document.getElementById('output-work-code').textContent = order.production_code;
                    document.getElementById('output-product').textContent = 
                        order.salesOrder?.items?.map(i => `${i.product.product_name} (${i.quantity})`).join(', ') || 'N/A';

                    document.getElementById('modal-output').classList.remove('d-none');
                }
            });
        });

        // Close modals
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.add('d-none');
            });
        });
        document.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById(btn.dataset.closeModal).classList.add('d-none');
            });
        });

        // Table filtering
        const searchInput = document.getElementById('search-input');
        const statusFilter = document.getElementById('status-filter');
        const deptFilter = document.getElementById('department-filter');
        const rows = document.querySelectorAll('#production-table tbody tr');

        function filterTable() {
            const search = (searchInput?.value || '').toLowerCase();
            const status = statusFilter?.value || '';
            const dept = deptFilter?.value || '';

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.querySelector('.badge')?.textContent.trim() || '';
                const rowDept = row.cells[3]?.textContent.trim() || '';

                const matches = text.includes(search) &&
                                (!status || rowStatus === status) &&
                                (!dept || rowDept === dept);

                row.style.display = matches ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterTable);
        statusFilter?.addEventListener('change', filterTable);
        deptFilter?.addEventListener('change', filterTable);
    </script>
@endsection