// Main Dashboard JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Comparison Chart
    const comparisonChartEl = document.getElementById('comparisonChart');
    if (comparisonChartEl) {
        new Chart(comparisonChartEl, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [
                    {
                        label: 'Ordered',
                        data: [],
                        backgroundColor: 'rgba(165,107,85,0.65)',
                        borderColor: '#A56B55',
                        borderWidth: 1
                    },
                    {
                        label: 'Produced',
                        data: [],
                        backgroundColor: 'rgba(99,54,39,0.65)',
                        borderColor: '#633627',
                        borderWidth: 1
                    },
                    {
                        label: 'Distributed',
                        data: [],
                        backgroundColor: 'rgba(217,184,169,0.65)',
                        borderColor: '#D9B8A9',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e9ecef' } }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }

    // Inventory Trend Chart
    const inventoryTrendChartEl = document.getElementById('inventoryTrendChart');
    if (inventoryTrendChartEl) {
        new Chart(inventoryTrendChartEl, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Inventory Level',
                    data: [],
                    borderColor: 'var(--color-primary)',
                    backgroundColor: 'rgba(165,107,85,0.12)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'var(--color-primary)',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: false, grid: { color: '#e9ecef' } }
                }
            }
        });
    }
});
