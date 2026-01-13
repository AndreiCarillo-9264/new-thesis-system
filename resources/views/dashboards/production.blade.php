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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table">
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

    @if($canEdit)
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
    @endif

    <!-- <script>
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
    </script> -->


@endsection