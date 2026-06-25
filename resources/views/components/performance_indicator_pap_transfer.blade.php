<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#performanceTable tbody tr.data-row').forEach(function (row) {
            const cells = row.children;
            if (cells.length < 3) {
                return;
            }

            const papCell = cells[0];
            const indicatorCell = cells[1];
            const indicatorHtml = indicatorCell.innerHTML.trim();
            const indicatorText = indicatorCell.textContent.trim().replace(/\s+/g, ' ').toLowerCase();
            const papCellClone = papCell.cloneNode(true);
            papCellClone.querySelectorAll('button, .pap-inline-performance-indicator').forEach(function (element) {
                element.remove();
            });
            const hasVisiblePapHierarchy = papCellClone.textContent.trim() !== '';

            if (!String(row.dataset.indicatorId || '').trim()) {
                indicatorCell.remove();
                return;
            }

            if (['n/a', 'na', 'not applicable'].includes(indicatorText)) {
                indicatorCell.remove();
                return;
            }

            if (!indicatorHtml || papCell.querySelector('.pap-inline-performance-indicator')) {
                indicatorCell.remove();
                return;
            }

            const wrapper = document.createElement('div');
            const isSubHierarchyNaRow = row.classList.contains('sub-hierarchy-na-row') || !hasVisiblePapHierarchy;
            if (isSubHierarchyNaRow) {
                row.classList.add('sub-hierarchy-na-row');
                Array.from(row.children).forEach(function (cell) {
                    cell.style.borderTop = '0';
                });
            }
            wrapper.className = isSubHierarchyNaRow
                ? 'pap-inline-performance-indicator mt-2'
                : 'pap-inline-performance-indicator mt-2 pt-2';
            if (!isSubHierarchyNaRow) {
                wrapper.style.borderTop = '1px solid rgba(37, 99, 235, 0.18)';
            }
            wrapper.style.color = '#1f2937';
            wrapper.style.fontWeight = '500';
            wrapper.innerHTML = indicatorHtml;

            papCell.appendChild(wrapper);
            indicatorCell.remove();
        });
    });
</script>
