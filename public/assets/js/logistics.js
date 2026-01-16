// New: public/assets/js/logistics.js
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const searchInput = document.getElementById('table-search');
    const recordDistributionBtn = document.getElementById('recordDistributionBtn');
    const newDistributionForm = document.getElementById('newDistributionForm');
    const newDistributionModalEl = document.getElementById('newDistributionModal');
    const recordTransferBtn = document.getElementById('recordTransferBtn');
    const newTransferForm = document.getElementById('newTransferForm');
    const newTransferModalEl = document.getElementById('newTransferModal');
    const joSelect = document.getElementById('jo-select');
    const productNameInput = document.getElementById('product-name');

    // Populate JO dropdown
    fetch('/logistics/job-orders')
        .then(res => res.json())
        .then(jobOrders => {
            joSelect.innerHTML = '<option value="">Select JO Number</option>';
            jobOrders.forEach(jo => {
                const option = document.createElement('option');
                option.value = jo.id;
                option.textContent = jo.jo_number;
                option.dataset.product = jo.product ? jo.product.product_name : '—';
                joSelect.appendChild(option);
            });
        })
        .catch(err => console.error(err));

    // Auto-fill product on JO select
    joSelect.addEventListener('change', function () {
        productNameInput.value = this.selectedOptions[0].dataset.product || '';
    });

    // Table refresh
    function refreshTables() {
        const search = searchInput.value.trim();
        fetch(`/logistics/search?search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(data => {
                // Recent Distributions
                const distTbody = document.querySelector('.distributions-table tbody');
                distTbody.innerHTML = '';
                if (data.distributions.length === 0) {
                    distTbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No recent distributions</td></tr>';
                } else {
                    data.distributions.forEach(dist => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${dist.job_order ? dist.job_order.jo_number : '—'}</td>
                            <td>${dist.product ? dist.product.product_name : '—'}</td>
                            <td>${new Intl.NumberFormat().format(dist.quantity_distributed)}</td>
                            <td>${new Date(dist.distribution_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                            <td>${dist.destination}</td>
                        `;
                        distTbody.appendChild(tr);
                    });
                }

                // Recent Transfers
                const transTbody = document.querySelector('.transfers-table tbody');
                transTbody.innerHTML = '';
                if (data.transfers.length === 0) {
                    transTbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No recent transfers</td></tr>';
                } else {
                    data.transfers.forEach(trans => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${trans.product ? trans.product.product_name : '—'}</td>
                            <td>${new Intl.NumberFormat().format(trans.quantity)}</td>
                            <td>${trans.from_location}</td>
                            <td>${trans.to_location}</td>
                            <td>${new Date(trans.transfer_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                        `;
                        transTbody.appendChild(tr);
                    });
                }
            })
            .catch(err => console.error(err));
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
    refreshTables(); // Initial load

    // Record Distribution
    recordDistributionBtn.addEventListener('click', function () {
        if (!newDistributionForm.checkValidity()) {
            newDistributionForm.reportValidity();
            return;
        }
        const formData = new FormData(newDistributionForm);
        fetch('/logistics/distributions', {
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
                    bootstrap.Modal.getInstance(newDistributionModalEl).hide();
                    refreshTables();
                }
            })
            .catch(err => console.error(err));
    });

    // Clear distribution form on close
    newDistributionModalEl.addEventListener('hidden.bs.modal', function () {
        newDistributionForm.reset();
        productNameInput.value = '';
    });

    // Record Transfer
    recordTransferBtn.addEventListener('click', function () {
        if (!newTransferForm.checkValidity()) {
            newTransferForm.reportValidity();
            return;
        }
        const formData = new FormData(newTransferForm);
        fetch('/logistics/transfers', {
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
});