@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-package.svg') }}" width="48" height="48" alt="Inventory">
@endsection

@section('page-title', 'Inventory Dashboard')
@section('page-subtitle', 'Monitor stock levels, track movements, and manage warehouse inventory')

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
                <h5>Total Products</h5>
                <h2>{{ $stats['total_products'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card {{ $stats['low_stock_count'] > 0 ? 'bg-warning-subtle' : '' }}">
                <h5>Low Stock Alerts</h5>
                <h2>{{ $stats['low_stock_count'] }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Total Stock Quantity</h5>
                <h2>{{ number_format($stats['total_quantity']) }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Warehouses Tracked</h5>
                <h2>{{ $stats['warehouse_count'] }}</h2>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-card-header d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3>Current Inventory Levels</h3>
                <p>View and manage stock across all products and warehouses</p>
            </div>

            @if($canEdit)
                <div class="d-flex align-items-center gap-2">
                    <button id="btn-adjust-stock" class="btn btn-outline-secondary d-flex align-items-center gap-2 px-3">
                        <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-edit-alt.svg') }}" width="18" height="18" alt="Adjust">
                        Adjust Stock
                    </button>
                    <button id="btn-new-movement" class="btn btn-primary d-flex align-items-center gap-2 px-3"
                            style="background-color: var(--color-primary); border-color: var(--color-primary);">
                        <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="18" height="18" alt="Movement">
                        Record Movement
                    </button>
                </div>
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
                            @if($canEdit)<th>Actions</th>@endif
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
                                @if($canEdit)
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary adjust-btn"
                                                data-id="{{ $inventory->id }}"
                                                data-product="{{ $inventory->product->product_code }} - {{ $inventory->product->product_name }}"
                                                data-stock="{{ $inventory->current_stock }}">
                                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-edit-alt.svg') }}" width="16" height="16">
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canEdit ? '9' : '8' }}" class="text-center text-muted py-5">
                                    No inventory records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!$canEdit)
                <div class="alert alert-info mt-4">
                    <small>ℹ️ You are viewing the Inventory dashboard in <strong>read-only</strong> mode. Only members of the Inventory department can record movements or adjust stock.</small>
                </div>
            @endif
        </div>
    </div>

    @if($canEdit)
        <!-- Record Movement Modal -->
        <div id="modal-movement" class="modal-overlay d-none">
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
        </div>

        <!-- Adjust Stock Modal -->
        <div id="modal-adjust" class="modal-overlay d-none">
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
        </div>
    @endif

    <script>
        // Open Record Movement Modal
        document.getElementById('btn-new-movement')?.addEventListener('click', () => {
            document.getElementById('modal-movement').classList.remove('d-none');
            document.getElementById('movement-form').reset();
            document.querySelector('#movement-form [name="movement_date"]').value = new Date().toISOString().split('T')[0];
        });

        // Open Adjust Stock Modal
        document.querySelectorAll('.adjust-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const product = btn.dataset.product;
                const stock = btn.dataset.stock;

                document.getElementById('adjust-inventory-id').value = id;
                document.getElementById('adjust-product-name').textContent = product;
                document.getElementById('adjust-current-stock').textContent = stock;

                document.getElementById('modal-adjust').classList.remove('d-none');
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

        // Client-side table filtering
        const searchInput = document.getElementById('search-input');
        const warehouseFilter = document.getElementById('warehouse-filter');
        const stockStatusFilter = document.getElementById('stock-status-filter');
        const rows = document.querySelectorAll('#inventory-table tbody tr');

        function filterTable() {
            const search = (searchInput?.value || '').toLowerCase();
            const warehouse = warehouseFilter?.value || '';
            const status = stockStatusFilter?.value || '';

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowWarehouse = row.cells[3]?.textContent.trim() || 'Not Set';
                const currentStock = parseInt(row.cells[4]?.textContent.replace(/,/g, '')) || 0;
                const reorderLevel = parseInt(row.cells[5]?.textContent) || 0;

                const isLow = currentStock <= reorderLevel && currentStock > 0;
                const isZero = currentStock === 0;
                const isNormal = currentStock > reorderLevel;

                const matchesSearch = text.includes(search);
                const matchesWarehouse = !warehouse || rowWarehouse === warehouse;
                const matchesStatus = !status ||
                    (status === 'low' && isLow) ||
                    (status === 'normal' && isNormal) ||
                    (status === 'zero' && isZero);

                row.style.display = matchesSearch && matchesWarehouse && matchesStatus ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterTable);
        warehouseFilter?.addEventListener('change', filterTable);
        stockStatusFilter?.addEventListener('change', filterTable);

        // Initial filter
        filterTable();
    </script>
@endsection