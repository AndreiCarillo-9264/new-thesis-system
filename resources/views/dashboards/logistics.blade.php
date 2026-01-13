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

    <!-- <script>
        // Open New Delivery
        document.getElementById('btn-new-delivery')?.addEventListener('click', () => {
            document.getElementById('modal-delivery-title').textContent = 'Schedule New Delivery';
            document.getElementById('delivery-form').reset();
            document.getElementById('delivery_id').value = '';
            document.getElementById('items-tbody').innerHTML = '';
            document.getElementById('modal-delivery').classList.remove('d-none');
        });

        // Load SO Items when selected — FIXED ROUTE GENERATION
        document.getElementById('sales_order_id')?.addEventListener('change', async function () {
            const soId = this.value;
            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';

            if (!soId) {
                tbody.innerHTML = '';
                return;
            }

            try {
                // Correct way to generate dynamic route with parameter
                const url = '{{ route("logistics.so.items", ":id") }}'.replace(':id', soId);
                const response = await fetch(url);

                if (!response.ok) throw new Error('Failed to load items');

                const items = await response.json();

                tbody.innerHTML = '';
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${item.product.product_code} - ${item.product.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>
                            <input type="number" name="item_qty[${item.id}]" class="form-control form-control-sm"
                                   min="0" max="${item.quantity}" value="${item.quantity}" required>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (err) {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="3" class="text-danger">Failed to load items</td></tr>';
            }
        });

        // View / Edit Delivery — also using correct route pattern
        document.querySelectorAll('.view-btn, .edit-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const isEdit = btn.classList.contains('edit-btn');

                const url = '{{ route("logistics.deliveries.show", ":id") }}'.replace(':id', id);
                const response = await fetch(url);
                const delivery = await response.json();

                if (isEdit) {
                    document.getElementById('modal-delivery-title').textContent = 'Edit Delivery';
                    document.getElementById('delivery_id').value = delivery.id;
                    document.getElementById('delivery_number').value = delivery.delivery_number;
                    document.getElementById('delivery_date').value = delivery.delivery_date || '';
                    document.getElementById('sales_order_id').value = delivery.sales_order_id;
                    document.getElementById('driver').value = delivery.driver || '';
                    document.getElementById('vehicle').value = delivery.vehicle || '';
                    document.getElementById('status_id').value = delivery.status_id;

                    // Trigger reload of items
                    document.getElementById('sales_order_id').dispatchEvent(new Event('change'));

                    document.getElementById('modal-delivery').classList.remove('d-none');
                } else {
                    document.getElementById('view-delivery-no').textContent = delivery.delivery_number;
                    document.getElementById('view-so-no').textContent = delivery.salesOrder?.so_number || '-';
                    document.getElementById('view-customer').textContent = delivery.salesOrder?.customer?.customer_name || '-';
                    document.getElementById('view-date').textContent = delivery.delivery_date ? new Date(delivery.delivery_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-';
                    document.getElementById('view-driver').textContent = delivery.driver || '-';
                    document.getElementById('view-vehicle').textContent = delivery.vehicle || '-';
                    document.getElementById('view-status').textContent = delivery.status.name;
                    document.getElementById('view-status').className = `badge bg-${delivery.status.name === 'Completed' ? 'success' : (delivery.status.name === 'In Transit' ? 'warning' : 'secondary')}`;

                    const viewTbody = document.getElementById('view-items-tbody');
                    viewTbody.innerHTML = '';
                    delivery.items.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${item.product.product_code} - ${item.product.product_name}</td><td>${item.quantity}</td>`;
                        viewTbody.appendChild(tr);
                    });

                    document.getElementById('modal-view-delivery').classList.remove('d-none');
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
        const rows = document.querySelectorAll('#deliveries-table tbody tr');

        function filterTable() {
            const search = (searchInput?.value || '').toLowerCase();
            const status = statusFilter?.value || '';

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.querySelector('.badge')?.textContent.trim() || '';

                row.style.display = (text.includes(search) && (!status || rowStatus === status)) ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterTable);
        statusFilter?.addEventListener('change', filterTable);
    </script> -->

@endsection