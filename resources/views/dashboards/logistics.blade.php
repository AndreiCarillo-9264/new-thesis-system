@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-truck.svg') }}" width="48" height="48" alt="Logistics">
@endsection

@section('page-title', 'Logistics Management')
@section('page-subtitle', 'Coordinate deliveries and track shipment schedules')

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
                <h5>Today's Deliveries</h5>
                <h2>{{ $stats['today_deliveries'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>In Transit</h5>
                <h2>{{ $stats['in_transit'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Completed Today</h5>
                <h2>{{ $stats['completed_today'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>On-Time Rate (This Month)</h5>
                <h2>{{ $stats['on_time_rate'] }}%</h2>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-card-header d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3>Delivery Schedule</h3>
                <p>Manage and track all scheduled deliveries</p>
            </div>

            @if($canEdit)
                <button id="btn-new-delivery" class="btn btn-primary d-flex align-items-center gap-2 px-3"
                        style="background-color: var(--color-primary); border-color: var(--color-primary);">
                    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="18" height="18" alt="New Delivery">
                    Schedule Delivery
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
                               placeholder="Search by delivery number, customer, or driver..." aria-label="Search deliveries">
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

            <!-- Table -->
            <div class="table-responsive">
                <table class="admin-table w-100 mb-0" id="deliveries-table">
                    <thead>
                        <tr>
                            <th>Delivery No.</th>
                            <th>SO Number</th>
                            <th>Customer</th>
                            <th>Delivery Date</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Total Items</th>
                            <th>Status</th>
                            @if($canEdit)<th>Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td><strong>{{ $delivery->delivery_number }}</strong></td>
                                <td>{{ $delivery->salesOrder?->so_number ?? '-' }}</td>
                                <td>{{ $delivery->salesOrder?->customer?->customer_name ?? '-' }}</td>
                                <td>{{ $delivery->delivery_date?->format('M d, Y') ?? '-' }}</td>
                                <td>{{ $delivery->driver ?? '-' }}</td>
                                <td>{{ $delivery->vehicle ?? '-' }}</td>
                                <td>{{ $delivery->items->sum('quantity') }}</td>
                                <td>
                                    <span class="badge bg-{{
                                        $delivery->status->name == 'Completed' ? 'success' :
                                        ($delivery->status->name == 'In Transit' ? 'warning' : 'secondary')
                                    }}">
                                        {{ $delivery->status->name }}
                                    </span>
                                </td>
                                @if($canEdit)
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1 view-btn" data-id="{{ $delivery->id }}">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-show.svg') }}" width="16" height="16">
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary edit-btn" data-id="{{ $delivery->id }}">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-edit.svg') }}" width="16" height="16">
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canEdit ? '9' : '8' }}" class="text-center text-muted py-5">
                                    No deliveries recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!$canEdit)
                <div class="alert alert-info mt-4">
                    <small>ℹ️ You are viewing the Logistics dashboard in <strong>read-only</strong> mode. Only members of the Logistics department can create or edit deliveries.</small>
                </div>
            @endif
        </div>
    </div>

    @if($canEdit)
        <!-- Delivery Modal (Create/Edit) -->
        <div id="modal-delivery" class="modal-overlay d-none">
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
                                    <!-- Filled via JS when SO selected -->
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
        </div>

        <!-- View Delivery Modal -->
        <div id="modal-view-delivery" class="modal-overlay d-none">
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
        </div>
    @endif

    <script>
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
    </script>
@endsection