// Production Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Open New Work Order
    const btnNewWorkOrder = document.getElementById('btn-new-work-order');
    if (btnNewWorkOrder) {
        btnNewWorkOrder.addEventListener('click', () => {
            const modalTitle = document.getElementById('modal-work-title');
            const form = document.getElementById('work-order-form');
            const workOrderId = document.getElementById('work-order-id');
            const modal = document.getElementById('modal-work-order');

            if (modalTitle) modalTitle.textContent = 'Create New Production Order';
            if (form) form.reset();
            if (workOrderId) workOrderId.value = '';
            if (modal) modal.classList.remove('d-none');
        });
    }

    // View / Edit / Output Buttons
    document.querySelectorAll('.view-btn, .edit-btn, .output-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const action = btn.classList.contains('view-btn') ? 'view' :
                          btn.classList.contains('edit-btn') ? 'edit' : 'output';

            const routeTemplate = btn.dataset.showRouteTemplate || '/production/orders/:id';
            const url = routeTemplate.replace(':id', id);

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Failed to load order');
                const order = await response.json();

                if (action === 'view') {
                    const viewCode = document.getElementById('view-code');
                    const viewDept = document.getElementById('view-dept');
                    const viewSo = document.getElementById('view-so');
                    const viewCustomer = document.getElementById('view-customer');
                    const viewStart = document.getElementById('view-start');
                    const viewEnd = document.getElementById('view-end');
                    const viewStatus = document.getElementById('view-status');
                    const viewProgress = document.getElementById('view-progress');
                    const viewRemarks = document.getElementById('view-remarks');
                    const modal = document.getElementById('modal-view');

                    if (viewCode) viewCode.textContent = order.production_code;
                    if (viewDept) viewDept.textContent = order.department;
                    if (viewSo) viewSo.textContent = order.salesOrder?.so_number || '-';
                    if (viewCustomer) viewCustomer.textContent = order.salesOrder?.customer?.customer_name || '-';
                    if (viewStart) viewStart.textContent = order.start_date || '-';
                    if (viewEnd) viewEnd.textContent = order.end_date || '-';
                    if (viewStatus) {
                        viewStatus.textContent = order.status.name;
                        viewStatus.className = `badge bg-${order.status.name === 'Completed' ? 'success' : 'primary'}`;
                    }
                    if (viewProgress) {
                        viewProgress.style.width = `${Math.min(order.progress_percentage, 100)}%`;
                        viewProgress.textContent = `${Math.round(order.progress_percentage)}%`;
                    }
                    if (viewRemarks) viewRemarks.textContent = order.remarks || '-';

                    if (modal) modal.classList.remove('d-none');

                } else if (action === 'edit') {
                    const modalTitle = document.getElementById('modal-work-title');
                    const workOrderId = document.getElementById('work-order-id');
                    const productionCode = document.getElementById('production_code');
                    const department = document.getElementById('department');
                    const salesOrderId = document.getElementById('sales_order_id');
                    const startDate = document.getElementById('start_date');
                    const endDate = document.getElementById('end_date');
                    const statusId = document.getElementById('status_id');
                    const remarks = document.getElementById('remarks');
                    const modal = document.getElementById('modal-work-order');

                    if (modalTitle) modalTitle.textContent = 'Edit Production Order';
                    if (workOrderId) workOrderId.value = order.id;
                    if (productionCode) productionCode.value = order.production_code;
                    if (department) department.value = order.department;
                    if (salesOrderId) salesOrderId.value = order.sales_order_id;
                    if (startDate) startDate.value = order.start_date || '';
                    if (endDate) endDate.value = order.end_date || '';
                    if (statusId) statusId.value = order.status_id;
                    if (remarks) remarks.value = order.remarks || '';

                    if (modal) modal.classList.remove('d-none');

                } else if (action === 'output') {
                    const outputProductionId = document.getElementById('output-production-id');
                    const outputWorkCode = document.getElementById('output-work-code');
                    const outputProduct = document.getElementById('output-product');
                    const modal = document.getElementById('modal-output');

                    if (outputProductionId) outputProductionId.value = order.id;
                    if (outputWorkCode) outputWorkCode.textContent = order.production_code;
                    if (outputProduct) {
                        outputProduct.textContent = 
                            order.salesOrder?.items?.map(i => `${i.product.product_name} (${i.quantity})`).join(', ') || 'N/A';
                    }

                    if (modal) modal.classList.remove('d-none');
                }
            } catch (err) {
                console.error('Error loading order:', err);
                alert('Failed to load order details');
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
    const deptFilter = document.getElementById('department-filter');
    const productionTable = document.getElementById('production-table');
    const rows = productionTable ? productionTable.querySelectorAll('tbody tr') : [];

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

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
    if (deptFilter) {
        deptFilter.addEventListener('change', filterTable);
    }
});
