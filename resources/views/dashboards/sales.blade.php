@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/sales.svg') }}" width="32" height="32" alt="Sales">
@endsection

@section('page-title', 'Sales Dashboard')
@section('page-subtitle', 'Manage job orders and track distributions')

@section('content')

    <!-- KPI Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Total Job Orders</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($totalJobOrders ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Open Job Orders</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($openJobOrders ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Delivered This Month</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($deliveredThisMonth ?? 0) }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="stats-card text-center">
                <h5 class="text-muted mb-1 fw-medium">Pending Deliveries</h5>
                <h2 class="mb-0 fw-bold text-primary">{{ number_format($pendingDeliveries ?? 0) }}</h2>
            </div>
        </div>
    </div>

    <!-- Recent Job Orders -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Job Orders</h5>
                <small class="text-muted">Latest created/updated job orders</small>
            </div>
            @can('create', App\Models\JobOrder::class)
                <a href="{{ route('job_orders.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    New Job Order
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
                            <tr><td colspan="5" class="text-center py-5 text-muted">No recent job orders</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Distributions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Distributions</h5>
                <small class="text-muted">Latest outbound shipments / deliveries</small>
            </div>
            @can('create', App\Models\Distribution::class)
                <a href="{{ route('distributions.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/icons/add.svg') }}" width="16" height="16" alt="">
                    New Distribution
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

    @cannot('create', App\Models\JobOrder::class)
        <div class="alert alert-info mt-4">
            <small>ℹ️ You are viewing in read-only mode. Only Sales department members can create or modify job orders and distributions.</small>
        </div>
    @endcannot

    @if($canEdit)
        <!-- New Sales Order Modal -->
        <!-- <div id="modal-new-order" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="modal-order-title">
                <div class="modal-header">
                    <div>
                        <h4 id="modal-order-title" class="modal-title">Create New Sales Order</h4>
                        <p class="modal-subtitle">Add customer details and order items</p>
                    </div>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="{{ route('sales.orders.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">SO Number *</label>
                                    <input type="text" name="so_number" class="form-control" placeholder="e.g., SO-2026-001" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Order Date *</label>
                                    <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer *</label>
                            <div class="input-group">
                                <select name="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->customer_code }} - {{ $customer->customer_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-add-customer">
                                    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="16" height="16">
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="delivery_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status_id" class="form-select" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" {{ $status->name == 'Pending' ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>Order Items</h5>
                        <div id="items-container">
                            <div class="row mb-3 item-row">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <select class="form-select" name="product_id[]" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->product_code }} - {{ $product->product_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary btn-quick-add-product">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="16" height="16">
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="quantity[]" placeholder="Qty" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" class="form-control" name="unit_price[]" placeholder="Unit Price" required>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-danger remove-item">×</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-item" class="btn btn-outline-secondary btn-sm mb-3">
                            + Add Another Item
                        </button>

                        <div class="form-group">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-new-order">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background-color: var(--color-primary); border-color: var(--color-primary);">
                                Create Sales Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- Edit Sales Order Modal -->
        <!-- <div id="modal-edit-order" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="modal-edit-order-title">
                <div class="modal-header">
                    <div>
                        <h4 id="modal-edit-order-title" class="modal-title">Edit Sales Order</h4>
                        <p class="modal-subtitle">Update customer details and order items</p>
                    </div>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">SO Number *</label>
                                    <input type="text" name="so_number" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Order Date *</label>
                                    <input type="date" name="order_date" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer *</label>
                            <div class="input-group">
                                <select name="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->customer_code }} - {{ $customer->customer_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-add-customer">
                                    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="16" height="16">
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="delivery_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status_id" class="form-select" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}">
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>Order Items</h5>
                        <div id="items-container"></div>

                        <button type="button" id="add-item" class="btn btn-outline-secondary btn-sm mb-3">
                            + Add Another Item
                        </button>

                        <div class="form-group">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-edit-order">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background-color: var(--color-primary); border-color: var(--color-primary);">
                                Update Sales Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- View Sales Order Modal -->
        <!-- <div id="modal-view-order" class="modal-overlay d-none">
            <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="modal-view-order-title">
                <div class="modal-header">
                    <div>
                        <h4 id="modal-view-order-title" class="modal-title">View Sales Order</h4>
                        <p class="modal-subtitle">Order details and items</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">SO Number</label>
                            <p id="view-so_number" class="fw-bold"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Order Date</label>
                            <p id="view-order_date"></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Customer</label>
                        <p id="view-customer" class="fw-bold"></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Expected Delivery Date</label>
                            <p id="view-delivery_date"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <p id="view-status" class="badge bg-primary"></p>
                        </div>
                    </div>

                    <hr>

                    <h5>Order Items</h5>
                    <table class="table table-sm" id="view-items-table">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <p id="view-remarks"></p>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-light" data-close-modal="modal-view-order">Close</button>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Quick Add Customer Modal -->
        <!-- <div id="modal-quick-add-customer" class="modal-overlay d-none">
            <div class="modal-panel" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Quick Add Customer</h4>
                </div>
                <div class="modal-body">
                    <form id="quick-add-customer-form" action="javascript:void(0)">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Customer Code *</label>
                            <input type="text" name="customer_code" class="form-control" required placeholder="e.g., CUST-001">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-quick-add-customer">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- Quick Add Product Modal -->
        <!-- <div id="modal-quick-add-product" class="modal-overlay d-none">
            <div class="modal-panel" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h4 class="modal-title">Quick Add Product</h4>
                </div>
                <div class="modal-body">
                    <form id="quick-add-product-form" action="javascript:void(0)">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Product Code *</label>
                            <input type="text" name="product_code" class="form-control" required placeholder="e.g., PROD-001">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="product_name" class="form-control" required>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-light" data-close-modal="modal-quick-add-product">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->
    @endif

    <!-- <script>
        // Helper: create new item row from template
        function createItemRow() {
            return document.getElementById('item-row-template').firstElementChild.cloneNode(true);
        }

        // Open New Order modal
        document.getElementById('btn-new-order')?.addEventListener('click', () => {
            document.getElementById('modal-new-order').classList.remove('d-none');
        });

        // Add item row (New modal)
        document.querySelector('#modal-new-order #add-item')?.addEventListener('click', () => {
            const container = document.querySelector('#modal-new-order #items-container');
            const newRow = container.querySelector('.item-row').cloneNode(true);
            newRow.querySelectorAll('input, select').forEach(el => el.value = '');
            container.appendChild(newRow);
        });

        // Add item row (Edit modal)
        document.querySelector('#modal-edit-order #add-item')?.addEventListener('click', () => {
            const container = document.querySelector('#modal-edit-order #items-container');
            container.appendChild(createItemRow());
        });

        // Remove item row
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-item')) {
                const container = e.target.closest('#items-container');
                const rows = container.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                }
            }
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

        // Quick Add Customer
        document.querySelectorAll('.btn-quick-add-customer').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('modal-quick-add-customer').classList.remove('d-none');
            });
        });

        document.getElementById('quick-add-customer-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            data.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route("resources.customers.store") }}', {
                    method: 'POST',
                    body: data,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response:', errorText);
                    throw new Error(`HTTP ${response.status}`);
                }

                const { id, text } = await response.json();

                document.querySelectorAll('select[name="customer_id"]').forEach(select => {
                    if ([...select.options].every(opt => opt.value !== id.toString())) {
                        const option = new Option(text, id, true, true);
                        select.add(option);
                    }
                });

                document.getElementById('modal-quick-add-customer').classList.add('d-none');
                form.reset();
                alert('Customer added successfully!');
            } catch (err) {
                console.error('Quick add customer error:', err);
                alert('Failed to add customer. Check browser console for details.');
            }
        });

        // Quick Add Product
        document.querySelectorAll('.btn-quick-add-product').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('modal-quick-add-product').classList.remove('d-none');
            });
        });

        document.getElementById('quick-add-product-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            data.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route("resources.products.store") }}', {
                    method: 'POST',
                    body: data,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response:', errorText);
                    throw new Error(`HTTP ${response.status}`);
                }

                const { id, text } = await response.json();

                document.querySelectorAll('select[name="product_id[]"]').forEach(select => {
                    if ([...select.options].every(opt => opt.value !== id.toString())) {
                        const option = new Option(text, id);
                        select.add(option);
                    }
                });

                document.getElementById('modal-quick-add-product').classList.add('d-none');
                form.reset();
                alert('Product added successfully!');
            } catch (err) {
                console.error('Quick add product error:', err);
                alert('Failed to add product. Check browser console for details.');
            }
        });

        // View / Edit Order
        document.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const action = btn.dataset.action;
                const id = btn.dataset.id;
                const showUrl = '{{ route("sales.orders.show", ":id") }}'.replace(':id', id);

                try {
                    const response = await fetch(showUrl);
                    if (!response.ok) throw new Error('Failed to load order');
                    const order = await response.json();

                    if (action === 'view') {
                        document.getElementById('view-so_number').innerText = order.so_number;
                        document.getElementById('view-order_date').innerText = new Date(order.order_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                        document.getElementById('view-customer').innerText = order.customer.customer_name;
                        document.getElementById('view-delivery_date').innerText = order.delivery_date ? new Date(order.delivery_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-';
                        document.getElementById('view-status').innerText = order.status.name;
                        document.getElementById('view-remarks').innerText = order.remarks || '-';

                        const tbody = document.querySelector('#view-items-table tbody');
                        tbody.innerHTML = '';
                        order.items.forEach(item => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${item.product.product_code} - ${item.product.product_name}</td>
                                <td>${item.quantity}</td>
                                <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                            `;
                            tbody.appendChild(tr);
                        });

                        document.getElementById('modal-view-order').classList.remove('d-none');

                    } else if (action === 'edit') {
                        const modal = document.getElementById('modal-edit-order');
                        const form = modal.querySelector('form');
                        form.action = '{{ route("sales.orders.update", ":id") }}'.replace(':id', id);

                        form.querySelector('[name="so_number"]').value = order.so_number;
                        form.querySelector('[name="order_date"]').value = order.order_date;
                        form.querySelector('[name="delivery_date"]').value = order.delivery_date || '';
                        form.querySelector('[name="customer_id"]').value = order.customer_id;
                        form.querySelector('[name="status_id"]').value = order.status_id;
                        form.querySelector('[name="remarks"]').value = order.remarks || '';

                        const container = form.querySelector('#items-container');
                        container.innerHTML = '';
                        if (order.items.length === 0) {
                            container.appendChild(createItemRow());
                        } else {
                            order.items.forEach(item => {
                                const row = createItemRow();
                                row.querySelector('[name="product_id[]"]').value = item.product_id;
                                row.querySelector('[name="quantity[]"]').value = item.quantity;
                                row.querySelector('[name="unit_price[]"]').value = item.unit_price;
                                container.appendChild(row);
                            });
                        }

                        modal.classList.remove('d-none');
                    }
                } catch (err) {
                    alert('Error loading order: ' + err.message);
                }
            });
        });

        // Generate Report
        document.getElementById('btn-generate-report')?.addEventListener('click', () => {
            window.location.href = '{{ route("sales.report") }}';
        });

        // Table search & filter
        const searchInput = document.getElementById('search-input');
        const statusFilter = document.getElementById('status-filter');
        const rows = document.querySelectorAll('#orders-table tbody tr');

        function filterTable() {
            const search = (searchInput?.value || '').toLowerCase();
            const status = statusFilter?.value || '';

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.querySelector('.badge')?.textContent.trim() || '';
                const matchesSearch = text.includes(search);
                const matchesStatus = !status || rowStatus === status;
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterTable);
        statusFilter?.addEventListener('change', filterTable);
    </script> -->

@endsection