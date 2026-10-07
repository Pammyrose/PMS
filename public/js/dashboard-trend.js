window.initializeDashboardTrend = function () {
const progressTrendData = JSON.parse(document.getElementById('dashboardTrendData').textContent);
        const progressTrendChart = document.getElementById('progressTrendChart');
        const progressTrendButtons = document.querySelectorAll('[data-progress-view]');

        function renderProgressTrend(view) {
            if (!progressTrendChart) {
                return;
            }

            const rows = progressTrendData[view] || [];
            const hasDelays = rows.some((row) => Number(row.delay || 0) > 0);

            progressTrendChart.classList.toggle('is-quarterly', view === 'quarterly');
            progressTrendChart.setAttribute('aria-label', `${view === 'quarterly' ? 'Quarterly' : 'Monthly'} delays chart`);

            if (!hasDelays) {
                progressTrendChart.innerHTML = '<div class="progress-trend-empty" style="grid-column: 1 / -1;">No delayed physical outputs for this selection.</div>';
                return;
            }

            progressTrendChart.innerHTML = rows.map((row) => {
                const barHeight = Math.max(0, Math.min(100, Number(row.progress || 0)));
                const delay = Number(row.delay || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });

                return `
                    <div class="progress-trend-item" title="${row.label}: ${delay} delayed">
                        <div class="progress-trend-value">${delay}</div>
                        <div class="progress-trend-bar-track" aria-hidden="true">
                            <div class="progress-trend-bar" style="height: ${Math.max(barHeight, 1)}%;"></div>
                        </div>
                        <div class="progress-trend-label">${row.label}</div>
                    </div>
                `;
            }).join('');
        }

        progressTrendButtons.forEach((button) => {
            button.addEventListener('click', () => {
                progressTrendButtons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');
                renderProgressTrend(button.dataset.progressView);
            });
        });

        renderProgressTrend('monthly');
    
};
