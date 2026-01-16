// New: public/assets/js/inventory.js
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const searchInput = document.getElementById('table-search');
    const statusFilter = document.getElementById('status-filter');
    const recordTransferBtn = document.getElementById('recordTransferBtn');
    const newTransferForm = document.getElementById('newTransferForm');
    const newTransferModalEl = document.getElementById('newTransferModal');
    const adjustStockBtn = document.getElementById('adjustStockBtn');
    const adjustStockForm = document.getElementById('adjustStockForm');
    const adjustStockModalEl = document.getElementById('adjustStockModal');
    const adjustProductSelect = document.getElementById('adjust-product-select');
    const quantityInput = adjustStockForm.querySelector('[name="quantity"]');
    const adjustmentTypeSelect = adjustStockForm.querySelector('[name="adjustment_type"]');
    const newQuantityInput = document.getElementById('new-quantity');

    // Real-time calculation for new quantity
    function calculateNewQuantity() {
        const current = parseInt(adjustProductSelect.selectedOptions[0].dataset.current) || 0;
        const qty = parseInt(quantityInput.value) || 0;
        const type = adjustmentTypeSelect.value;
        newQuantityInput.value = type === 'add' ? current + qty : current - qty;
    }

    adjustProductSelect.addEventListener('change', calculateNewQuantity);
    quantityInput.addEventListener('input', calculateNewQuantity);
    adjustmentTypeSelect.addEventListener('change', calculateNewQuantity);

    // Table refresh
    function refreshTables() {
        const search = searchInput.value.trim();
        const status = statusFilter.value;
        fetch(`/inventory/search?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`)
            .then(res => res.json())
            .then(data => {
                // Current Inventory Levels
                const inventoryTbody = document.querySelector('.inventory-levels-table tbody');
                inventoryTbody.innerHTML = '';
                if (data.inventory.length === 0) {
                    inventoryTbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No inventory records found</td></tr>';
                } else {
                    data.inventory.forEach(inv => {
                        const tr = document.createElement('tr');
                        tr.dataset.status = inv.status;
                        tr.innerHTML = `
                            <td>${inv.product ? inv.product.product_code : '—'}</td>
                            <td>${inv.product ? inv.product.product_name : '—'}</td>
                            <td>${inv.product ? inv.product.category : '—'}</td>
                            <td>${inv.product ? inv.product.unit : '—'}</td>
                            <td>${new Intl.NumberFormat().format(inv.actual_quantity)}</td>
                            <td>${inv.last_counted_at ? new Date(inv.last_counted_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                            <td><span class="badge rounded-pill px-3 py-2 bg-${getStatusColor(inv.status)}">${formatStatus(inv.status)}</span></td>
                        `;
                        inventoryTbody.appendChild(tr);
                    });
                }

                // Recent Transfers
                const transfersTbody = document.querySelector('.transfers-table tbody');
                transfersTbody.innerHTML = '';
                if (data.transfers.length === 0) {
                    transfersTbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No recent transfers</td></tr>';
                } else {
                    data.transfers.forEach(transfer => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${transfer.product ? transfer.product.product_name : '—'}</td>
                            <td>${new Intl.NumberFormat().format(transfer.quantity)}</td>
                            <td>${transfer.from_location}</td>
                            <td>${transfer.to_location}</td>
                            <td>${new Date(transfer.transfer_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                        `;
                        transfersTbody.appendChild(tr);
                    });
                }
            })
            .catch(err => console.error(err));
    }

    function getStatusColor(status) {
        switch (status) {
            case 'low': return 'danger';
            case 'adequate': return 'success';
            case 'overstocked': return 'warning';
            default: return 'secondary';
        }
    }

    function formatStatus(status) {
        return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
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

    // Record Transfer
    recordTransferBtn.addEventListener('click', function () {
        if (!newTransferForm.checkValidity()) {
            newTransferForm.reportValidity();
            return;
        }
        const formData = new FormData(newTransferForm);
        fetch('/inventory/transfer', {
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
                    bootstrap.Modal.getInstance(newTransferModalEl).hide();
                    refreshTables();
                }
            })
            .catch(err => console.error(err));
    });

    // Clear transfer form on close
    newTransferModalEl.addEventListener('hidden.bs.modal', function () {
        newTransferForm.reset();
    });

    // Adjust Stock
    adjustStockBtn.addEventListener('click', function () {
        if (!adjustStockForm.checkValidity()) {
            adjustStockForm.reportValidity();
            return;
        }
        const formData = new FormData(adjustStockForm);
        fetch('/inventory/adjust', {
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
                    bootstrap.Modal.getInstance(adjustStockModalEl).hide();
                    refreshTables();
                }
            })
            .catch(err => console.error(err));
    });

    // Clear adjust form on close
    adjustStockModalEl.addEventListener('hidden.bs.modal', function () {
        adjustStockForm.reset();
        newQuantityInput.value = '';
    });
});