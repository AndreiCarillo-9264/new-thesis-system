// New: public/assets/js/production.js
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const searchInput = document.getElementById('table-search');
    const statusFilter = document.getElementById('status-filter');
    const createWorkOrderBtn = document.getElementById('createWorkOrderBtn');
    const createWorkOrderForm = document.getElementById('createWorkOrderForm');
    const createWorkOrderModalEl = document.getElementById('createWorkOrderModal');
    const recordOutputBtn = document.getElementById('recordOutputBtn');
    const recordFinishedGoodsForm = document.getElementById('recordFinishedGoodsForm');
    const recordFinishedGoodsModalEl = document.getElementById('recordFinishedGoodsModal');
    const joNumberSelect = document.getElementById('jo-number-select');
    const productNameInput = document.getElementById('product-name');

    // Populate JO Number dropdown in Record Finished Goods modal
    fetch('/production/job-orders')
        .then(res => res.json())
        .then(orders => {
            joNumberSelect.innerHTML = '<option value="">Select JO Number</option>';
            orders.forEach(order => {
                const option = document.createElement('option');
                option.value = order.id;
                option.textContent = order.jo_number;
                option.dataset.product = order.product ? order.product.product_name : '—';
                joNumberSelect.appendChild(option);
            });
        })
        .catch(err => console.error(err));

    // Auto-fill product name on JO selection
    joNumberSelect.addEventListener('change', function () {
        productNameInput.value = this.selectedOptions[0].dataset.product || '';
    });

    // Table refresh for both tables
    function refreshTables() {
        const search = searchInput.value.trim();
        const status = statusFilter.value;
        fetch(`/production/search?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`)
            .then(res => res.json())
            .then(data => {
                // Pending Job Orders
                const pendingTbody = document.querySelector('.pending-jobs-table tbody');
                pendingTbody.innerHTML = '';
                if (data.pending.length === 0) {
                    pendingTbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No pending job orders</td></tr>';
                } else {
                    data.pending.forEach(order => {
                        const tr = document.createElement('tr');
                        tr.dataset.status = order.status;
                        tr.innerHTML = `
                            <td class="fw-medium">${order.jo_number}</td>
                            <td>${order.product ? order.product.product_name : '—'}</td>
                            <td>${new Intl.NumberFormat().format(order.ordered_quantity)}</td>
                            <td>${new Date(order.jo_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                            <td><span class="badge rounded-pill px-3 py-2 bg-${getStatusColor(order.status)}">${formatStatus(order.status)}</span></td>
                        `;
                        pendingTbody.appendChild(tr);
                    });
                }

                // Recent Finished Goods
                const finishedTbody = document.querySelector('.finished-goods-table tbody');
                finishedTbody.innerHTML = '';
                if (data.finished.length === 0) {
                    finishedTbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">No recent production output</td></tr>';
                } else {
                    data.finished.forEach(fg => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="fw-medium">${fg.job_order ? fg.job_order.jo_number : '—'}</td>
                            <td>${fg.product ? fg.product.product_name : '—'}</td>
                            <td>${new Intl.NumberFormat().format(fg.quantity_produced)}</td>
                            <td>${new Date(fg.production_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                        `;
                        finishedTbody.appendChild(tr);
                    });
                }
            })
            .catch(err => console.error(err));
    }

    function getStatusColor(status) {
        switch (status) {
            case 'open': return 'warning';
            case 'in_progress': return 'info';
            case 'completed': return 'success';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }

    function formatStatus(status) {
        return status.replace(/_/g, ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }

    // Debounce for search
    function debounce(func, delay) {
        let timeout;
        return function () {
            clearTimeout(timeout);
            timeout = setTimeout(func, delay);
        };
    }

    searchInput.addEventListener('input', debounce(refreshTables, 300));
    statusFilter.addEventListener('change', refreshTables);
    refreshTables(); // Initial load

    // Create Work Order
    createWorkOrderBtn.addEventListener('click', function () {
        if (!createWorkOrderForm.checkValidity()) {
            createWorkOrderForm.reportValidity();
            return;
        }
        const formData = new FormData(createWorkOrderForm);
        fetch('/production/job-orders', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(createWorkOrderModalEl).hide();
                    refreshTables();
                }
            })
            .catch(err => console.error(err));
    });

    // Clear create form on close
    createWorkOrderModalEl.addEventListener('hidden.bs.modal', function () {
        createWorkOrderForm.reset();
    });

    // Record Finished Goods
    recordOutputBtn.addEventListener('click', function () {
        if (!recordFinishedGoodsForm.checkValidity()) {
            recordFinishedGoodsForm.reportValidity();
            return;
        }
        const formData = new FormData(recordFinishedGoodsForm);
        fetch('/production/finished-goods', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(recordFinishedGoodsModalEl).hide();
                    refreshTables();
                }
            })
            .catch(err => console.error(err));
    });

    // Clear record form on close
    recordFinishedGoodsModalEl.addEventListener('hidden.bs.modal', function () {
        recordFinishedGoodsForm.reset();
        productNameInput.value = '';
    });
});