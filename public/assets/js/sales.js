// Sales Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Helper: create new item row from template
    function createItemRow() {
        const template = document.getElementById('item-row-template');
        if (template && template.firstElementChild) {
            return template.firstElementChild.cloneNode(true);
        }
        // Fallback: create row manually if template doesn't exist
        const row = document.createElement('div');
        row.className = 'row mb-3 item-row';
        row.innerHTML = `
            <div class="col-md-5">
                <div class="input-group">
                    <select class="form-select" name="product_id[]" required>
                        <option value="">Select Product</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary btn-quick-add-product">
                        +
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
        `;
        return row;
    }

    // Open New Order modal
    const btnNewOrder = document.getElementById('btn-new-order');
    if (btnNewOrder) {
        btnNewOrder.addEventListener('click', () => {
            const modal = document.getElementById('modal-new-order');
            if (modal) modal.classList.remove('d-none');
        });
    }

    // Add item row (New modal)
    const addItemBtnNew = document.querySelector('#modal-new-order #add-item');
    if (addItemBtnNew) {
        addItemBtnNew.addEventListener('click', () => {
            const container = document.querySelector('#modal-new-order #items-container');
            if (container) {
                const existingRow = container.querySelector('.item-row');
                if (existingRow) {
                    const newRow = existingRow.cloneNode(true);
                    newRow.querySelectorAll('input, select').forEach(el => el.value = '');
                    container.appendChild(newRow);
                } else {
                    container.appendChild(createItemRow());
                }
            }
        });
    }

    // Add item row (Edit modal)
    const addItemBtnEdit = document.querySelector('#modal-edit-order #add-item');
    if (addItemBtnEdit) {
        addItemBtnEdit.addEventListener('click', () => {
            const container = document.querySelector('#modal-edit-order #items-container');
            if (container) {
                container.appendChild(createItemRow());
            }
        });
    }

    // Remove item row
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item')) {
            const container = e.target.closest('#items-container');
            if (container) {
                const rows = container.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                }
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
            const modalId = btn.dataset.closeModal;
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('d-none');
        });
    });

    // Quick Add Customer
    document.querySelectorAll('.btn-quick-add-customer').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById('modal-quick-add-customer');
            if (modal) modal.classList.remove('d-none');
        });
    });

    const quickAddCustomerForm = document.getElementById('quick-add-customer-form');
    if (quickAddCustomerForm) {
        quickAddCustomerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                data.append('_token', csrfToken);
            }

            const routeUrl = form.dataset.routeUrl || '/resources/customers';

            try {
                const response = await fetch(routeUrl, {
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

                const modal = document.getElementById('modal-quick-add-customer');
                if (modal) modal.classList.add('d-none');
                form.reset();
                alert('Customer added successfully!');
            } catch (err) {
                console.error('Quick add customer error:', err);
                alert('Failed to add customer. Check browser console for details.');
            }
        });
    }

    // Quick Add Product
    document.querySelectorAll('.btn-quick-add-product').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById('modal-quick-add-product');
            if (modal) modal.classList.remove('d-none');
        });
    });

    const quickAddProductForm = document.getElementById('quick-add-product-form');
    if (quickAddProductForm) {
        quickAddProductForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                data.append('_token', csrfToken);
            }

            const routeUrl = form.dataset.routeUrl || '/resources/products';

            try {
                const response = await fetch(routeUrl, {
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

                const modal = document.getElementById('modal-quick-add-product');
                if (modal) modal.classList.add('d-none');
                form.reset();
                alert('Product added successfully!');
            } catch (err) {
                console.error('Quick add product error:', err);
                alert('Failed to add product. Check browser console for details.');
            }
        });
    }

    // View / Edit Order
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const action = btn.dataset.action;
            const id = btn.dataset.id;
            const showRouteTemplate = btn.dataset.showRouteTemplate || '/sales/orders/:id';
            const showUrl = showRouteTemplate.replace(':id', id);

            try {
                const response = await fetch(showUrl);
                if (!response.ok) throw new Error('Failed to load order');
                const order = await response.json();

                if (action === 'view') {
                    const viewSoNumber = document.getElementById('view-so_number');
                    const viewOrderDate = document.getElementById('view-order_date');
                    const viewCustomer = document.getElementById('view-customer');
                    const viewDeliveryDate = document.getElementById('view-delivery_date');
                    const viewStatus = document.getElementById('view-status');
                    const viewRemarks = document.getElementById('view-remarks');
                    const viewItemsTable = document.getElementById('view-items-table');
                    const modal = document.getElementById('modal-view-order');

                    if (viewSoNumber) viewSoNumber.innerText = order.so_number;
                    if (viewOrderDate) {
                        viewOrderDate.innerText = new Date(order.order_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    }
                    if (viewCustomer) viewCustomer.innerText = order.customer.customer_name;
                    if (viewDeliveryDate) {
                        viewDeliveryDate.innerText = order.delivery_date ? 
                            new Date(order.delivery_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-';
                    }
                    if (viewStatus) {
                        viewStatus.innerText = order.status.name;
                    }
                    if (viewRemarks) viewRemarks.innerText = order.remarks || '-';

                    if (viewItemsTable) {
                        const tbody = viewItemsTable.querySelector('tbody');
                        if (tbody) {
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
                        }
                    }

                    if (modal) modal.classList.remove('d-none');

                } else if (action === 'edit') {
                    const modal = document.getElementById('modal-edit-order');
                    const form = modal?.querySelector('form');
                    if (!form) return;

                    const updateRouteTemplate = btn.dataset.updateRouteTemplate || '/sales/orders/:id';
                    form.action = updateRouteTemplate.replace(':id', id);

                    const soNumberInput = form.querySelector('[name="so_number"]');
                    const orderDateInput = form.querySelector('[name="order_date"]');
                    const deliveryDateInput = form.querySelector('[name="delivery_date"]');
                    const customerSelect = form.querySelector('[name="customer_id"]');
                    const statusSelect = form.querySelector('[name="status_id"]');
                    const remarksTextarea = form.querySelector('[name="remarks"]');
                    const container = form.querySelector('#items-container');

                    if (soNumberInput) soNumberInput.value = order.so_number;
                    if (orderDateInput) orderDateInput.value = order.order_date;
                    if (deliveryDateInput) deliveryDateInput.value = order.delivery_date || '';
                    if (customerSelect) customerSelect.value = order.customer_id;
                    if (statusSelect) statusSelect.value = order.status_id;
                    if (remarksTextarea) remarksTextarea.value = order.remarks || '';

                    if (container) {
                        container.innerHTML = '';
                        if (order.items.length === 0) {
                            container.appendChild(createItemRow());
                        } else {
                            order.items.forEach(item => {
                                const row = createItemRow();
                                const productSelect = row.querySelector('[name="product_id[]"]');
                                const quantityInput = row.querySelector('[name="quantity[]"]');
                                const unitPriceInput = row.querySelector('[name="unit_price[]"]');
                                if (productSelect) productSelect.value = item.product_id;
                                if (quantityInput) quantityInput.value = item.quantity;
                                if (unitPriceInput) unitPriceInput.value = item.unit_price;
                                container.appendChild(row);
                            });
                        }
                    }

                    if (modal) modal.classList.remove('d-none');
                }
            } catch (err) {
                alert('Error loading order: ' + err.message);
            }
        });
    });

    // Generate Report
    const btnGenerateReport = document.getElementById('btn-generate-report');
    if (btnGenerateReport) {
        btnGenerateReport.addEventListener('click', () => {
            const reportUrl = btnGenerateReport.dataset.reportUrl || '/sales/report';
            window.location.href = reportUrl;
        });
    }

    // Table search & filter
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const ordersTable = document.getElementById('orders-table');
    const rows = ordersTable ? ordersTable.querySelectorAll('tbody tr') : [];

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

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
});
