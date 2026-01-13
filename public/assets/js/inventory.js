// Inventory Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Open Record Movement Modal
    const btnNewMovement = document.getElementById('btn-new-movement');
    if (btnNewMovement) {
        btnNewMovement.addEventListener('click', () => {
            const modal = document.getElementById('modal-movement');
            const form = document.getElementById('movement-form');
            if (modal && form) {
                modal.classList.remove('d-none');
                form.reset();
                const dateInput = form.querySelector('[name="movement_date"]');
                if (dateInput) {
                    dateInput.value = new Date().toISOString().split('T')[0];
                }
            }
        });
    }

    // Open Adjust Stock Modal
    document.querySelectorAll('.adjust-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const product = btn.dataset.product;
            const stock = btn.dataset.stock;

            const inventoryIdInput = document.getElementById('adjust-inventory-id');
            const productNameEl = document.getElementById('adjust-product-name');
            const currentStockEl = document.getElementById('adjust-current-stock');
            const modal = document.getElementById('modal-adjust');

            if (inventoryIdInput) inventoryIdInput.value = id;
            if (productNameEl) productNameEl.textContent = product;
            if (currentStockEl) currentStockEl.textContent = stock;
            if (modal) modal.classList.remove('d-none');
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

    // Client-side table filtering
    const searchInput = document.getElementById('search-input');
    const warehouseFilter = document.getElementById('warehouse-filter');
    const stockStatusFilter = document.getElementById('stock-status-filter');
    const inventoryTable = document.getElementById('inventory-table');
    const rows = inventoryTable ? inventoryTable.querySelectorAll('tbody tr') : [];

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

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    if (warehouseFilter) {
        warehouseFilter.addEventListener('change', filterTable);
    }
    if (stockStatusFilter) {
        stockStatusFilter.addEventListener('change', filterTable);
    }

    // Initial filter
    if (rows.length > 0) {
        filterTable();
    }
});
