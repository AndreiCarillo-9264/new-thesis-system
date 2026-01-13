// Logistics Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Open New Delivery
    const btnNewDelivery = document.getElementById('btn-new-delivery');
    if (btnNewDelivery) {
        btnNewDelivery.addEventListener('click', () => {
            const modalTitle = document.getElementById('modal-delivery-title');
            const form = document.getElementById('delivery-form');
            const deliveryId = document.getElementById('delivery_id');
            const itemsTbody = document.getElementById('items-tbody');
            const modal = document.getElementById('modal-delivery');

            if (modalTitle) modalTitle.textContent = 'Schedule New Delivery';
            if (form) form.reset();
            if (deliveryId) deliveryId.value = '';
            if (itemsTbody) itemsTbody.innerHTML = '';
            if (modal) modal.classList.remove('d-none');
        });
    }

    // Load SO Items when selected — FIXED ROUTE GENERATION
    const salesOrderSelect = document.getElementById('sales_order_id');
    if (salesOrderSelect) {
        salesOrderSelect.addEventListener('change', async function () {
            const soId = this.value;
            const tbody = document.getElementById('items-tbody');
            if (!tbody) return;

            tbody.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';

            if (!soId) {
                tbody.innerHTML = '';
                return;
            }

            try {
                // Get the route from data attribute or construct it
                const routeTemplate = salesOrderSelect.dataset.routeTemplate || '/logistics/sales-orders/:id/items';
                const url = routeTemplate.replace(':id', soId);
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
    }

    // View / Edit Delivery — also using correct route pattern
    document.querySelectorAll('.view-btn, .edit-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const isEdit = btn.classList.contains('edit-btn');

            const routeTemplate = btn.dataset.showRouteTemplate || '/logistics/deliveries/:id';
            const url = routeTemplate.replace(':id', id);

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Failed to load delivery');
                const delivery = await response.json();

                if (isEdit) {
                    const modalTitle = document.getElementById('modal-delivery-title');
                    const deliveryId = document.getElementById('delivery_id');
                    const deliveryNumber = document.getElementById('delivery_number');
                    const deliveryDate = document.getElementById('delivery_date');
                    const salesOrderId = document.getElementById('sales_order_id');
                    const driver = document.getElementById('driver');
                    const vehicle = document.getElementById('vehicle');
                    const statusId = document.getElementById('status_id');
                    const modal = document.getElementById('modal-delivery');

                    if (modalTitle) modalTitle.textContent = 'Edit Delivery';
                    if (deliveryId) deliveryId.value = delivery.id;
                    if (deliveryNumber) deliveryNumber.value = delivery.delivery_number;
                    if (deliveryDate) deliveryDate.value = delivery.delivery_date || '';
                    if (salesOrderId) salesOrderId.value = delivery.sales_order_id;
                    if (driver) driver.value = delivery.driver || '';
                    if (vehicle) vehicle.value = delivery.vehicle || '';
                    if (statusId) statusId.value = delivery.status_id;

                    // Trigger reload of items
                    if (salesOrderId) {
                        salesOrderId.dispatchEvent(new Event('change'));
                    }

                    if (modal) modal.classList.remove('d-none');
                } else {
                    const viewDeliveryNo = document.getElementById('view-delivery-no');
                    const viewSoNo = document.getElementById('view-so-no');
                    const viewCustomer = document.getElementById('view-customer');
                    const viewDate = document.getElementById('view-date');
                    const viewDriver = document.getElementById('view-driver');
                    const viewVehicle = document.getElementById('view-vehicle');
                    const viewStatus = document.getElementById('view-status');
                    const viewItemsTbody = document.getElementById('view-items-tbody');
                    const modal = document.getElementById('modal-view-delivery');

                    if (viewDeliveryNo) viewDeliveryNo.textContent = delivery.delivery_number;
                    if (viewSoNo) viewSoNo.textContent = delivery.salesOrder?.so_number || '-';
                    if (viewCustomer) viewCustomer.textContent = delivery.salesOrder?.customer?.customer_name || '-';
                    if (viewDate) {
                        viewDate.textContent = delivery.delivery_date ? 
                            new Date(delivery.delivery_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-';
                    }
                    if (viewDriver) viewDriver.textContent = delivery.driver || '-';
                    if (viewVehicle) viewVehicle.textContent = delivery.vehicle || '-';
                    if (viewStatus) {
                        viewStatus.textContent = delivery.status.name;
                        viewStatus.className = `badge bg-${delivery.status.name === 'Completed' ? 'success' : (delivery.status.name === 'In Transit' ? 'warning' : 'secondary')}`;
                    }

                    if (viewItemsTbody) {
                        viewItemsTbody.innerHTML = '';
                        delivery.items.forEach(item => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `<td>${item.product.product_code} - ${item.product.product_name}</td><td>${item.quantity}</td>`;
                            viewItemsTbody.appendChild(tr);
                        });
                    }

                    if (modal) modal.classList.remove('d-none');
                }
            } catch (err) {
                console.error('Error loading delivery:', err);
                alert('Failed to load delivery details');
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
            const modalId = btn.dataset.closeModal;
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('d-none');
        });
    });

    // Table filtering
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const deliveriesTable = document.getElementById('deliveries-table');
    const rows = deliveriesTable ? deliveriesTable.querySelectorAll('tbody tr') : [];

    function filterTable() {
        const search = (searchInput?.value || '').toLowerCase();
        const status = statusFilter?.value || '';

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.querySelector('.badge')?.textContent.trim() || '';

            row.style.display = (text.includes(search) && (!status || rowStatus === status)) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
});
