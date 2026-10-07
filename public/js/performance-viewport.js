window.pmsRowsNearViewport = function (rows) {
    const candidates = Array.from(rows).filter(row => row.style.display !== 'none' && !row.classList.contains('d-none'));
    const container = document.querySelector('#performanceTable')?.closest('.table-container');
    if (!container) return candidates;
    const bounds = container.getBoundingClientRect();
    const top = Math.max(bounds.top, 0) - 250;
    const bottom = Math.min(bounds.bottom, window.innerHeight) + 250;
    return candidates.filter(row => {
        const rect = row.getBoundingClientRect();
        return rect.bottom >= top && rect.top <= bottom;
    });
};
