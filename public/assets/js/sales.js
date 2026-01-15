document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const searchInput = document.getElementById('table-search');
    const statusFilter = document.getElementById('status-filter');
    const createOrderBtn = document.getElementById('createOrderBtn');
    const createOrderForm = document.getElementById('createOrderForm');
    const createOrderModalEl = document.getElementById('createOrderModal');
    const quantityInput = createOrderForm.querySelector('[name="ordered_quantity"]');
    const priceInput = createOrderForm.querySelector('[name="unit_price"]');
    const totalInput = document.getElementById('total_amount');
    const generateReportBtn = document.getElementById('generateReportBtn');
    const generateReportForm = document.getElementById('generateReportForm');
    const generateReportModalEl = document.getElementById('generateReportModal');

    // Table refresh
    function refreshTable() {
        const search = searchInput.value.trim();
        const status = statusFilter.value;
        // Inside refreshTable function
        fetch(`/sales/orders/search?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`)
            .then(res => res.json())
            .then(data => {
                // Pending Orders
                const pendingTbody = document.querySelector('.sales-orders-table tbody');
                pendingTbody.innerHTML = '';
                if (data.orders.length === 0) {
                    pendingTbody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted">No sales orders</td></tr>';
                } else {
                    data.orders.forEach(order => {
                        const tr = document.createElement('tr');
                        tr.dataset.status = order.status;
                        tr.innerHTML = `
                            <td class="fw-medium">${order.jo_number}</td>
                            <td>${order.customer_name || '—'}</td>
                            <td>${order.product ? order.product.product_name : '—'}</td>
                            <td>${new Intl.NumberFormat().format(order.ordered_quantity)}</td>
                            <td>$${new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(order.total_amount)}</td>
                            <td><span class="badge rounded-pill px-3 py-2 bg-${getStatusColor(order.status)}">${formatStatus(order.status)}</span></td>
                            <td><span class="badge rounded-pill px-3 py-2 bg-${getProdColor(order.production_status)}">${formatStatus(order.production_status)}</span></td>
                            <td>${order.due_date ? new Date(order.due_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                            <td>${order.sales_rep ? order.sales_rep.name : '—'}</td>
                        `;
                        pendingTbody.appendChild(tr);
                    });
                }

                // Completed Orders
                const completedTbody = document.querySelector('.completed-orders-table tbody');
                completedTbody.innerHTML = '';
                if (data.distributions.length === 0) {
                    completedTbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No completed orders</td></tr>';
                } else {
                    data.distributions.forEach(dist => {
                        const order = dist.job_order;
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="fw-medium">${order ? order.jo_number : '—'}</td>
                            <td>${order ? order.customer_name : '—'}</td>
                            <td>${order && order.product ? order.product.product_name : '—'}</td>
                            <td>${new Intl.NumberFormat().format(dist.quantity_distributed)}</td>
                            <td>${dist.distribution_date ? new Date(dist.distribution_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                            <td><span class="badge rounded-pill px-3 py-2 bg-success">Delivered</span></td>
                        `;
                        completedTbody.appendChild(tr);
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

    function getProdColor(status) {
        switch (status) {
            case 'pending': return 'secondary';
            case 'in_production': return 'warning';
            case 'completed': return 'success';
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

    searchInput.addEventListener('input', debounce(refreshTable, 300));
    statusFilter.addEventListener('change', refreshTable);
    refreshTable(); // Initial load

    // Calculate total
    function calculateTotal() {
        const qty = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalInput.value = (qty * price).toFixed(2);
    }

    quantityInput.addEventListener('input', calculateTotal);
    priceInput.addEventListener('input', calculateTotal);

    // Create order
    createOrderBtn.addEventListener('click', function () {
        if (!createOrderForm.checkValidity()) {
            createOrderForm.reportValidity();
            return;
        }
        const formData = new FormData(createOrderForm);
        fetch('/sales/orders', {
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
                    bootstrap.Modal.getInstance(createOrderModalEl).hide();
                    refreshTable();
                    // Optional: show success toast
                }
            })
            .catch(err => console.error(err));
    });

    // Clear create form on close
    createOrderModalEl.addEventListener('hidden.bs.modal', function () {
        createOrderForm.reset();
        totalInput.value = '';
    });

    // Generate report
    generateReportBtn.addEventListener('click', function () {
        if (!generateReportForm.checkValidity()) {
            generateReportForm.reportValidity();
            return;
        }
        const formData = new FormData(generateReportForm);
        fetch('/sales/reports/generate', {
            method: 'POST',
            body: formData
        })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        console.error(err); // Handle errors, e.g., display in modal
                        throw new Error('Validation failed');
                    });
                }
                return res.blob();
            })
            .then(blob => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `sales_report.${generateReportForm.format.value.toLowerCase()}`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
                bootstrap.Modal.getInstance(generateReportModalEl).hide();
            })
            .catch(err => console.error(err));
    });

    // Clear report form on close
    generateReportModalEl.addEventListener('hidden.bs.modal', function () {
        generateReportForm.reset();
    });
});