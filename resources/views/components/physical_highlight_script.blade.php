<style>
  /* Nested column menus must open only through Bootstrap's click-added .show state. */
  #columnOptionsDropdown + .dropdown-menu .dropend > .dropdown-menu:not(.show) {
    display: none !important;
  }

  #performanceTable tr.dashboard-highlight-row > td {
    animation: dashboardIndicatorPulse 1.4s ease-in-out 2;
    background-color: #fff7cc !important;
    box-shadow: inset 4px 0 0 #f59e0b;
  }

  @keyframes dashboardIndicatorPulse {
    0%, 100% {
      background-color: #fff7cc;
    }

    50% {
      background-color: #fde68a;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const rowId = String(params.get('highlight_row_id') || '').trim();
    const indicatorId = String(params.get('highlight_indicator_id') || '').trim();

    if (!rowId && !indicatorId) {
      return;
    }

    const rows = Array.from(document.querySelectorAll('#performanceTable tbody tr[data-row-id]'));
    const idMatches = (value, expected) => expected && String(value || '') === expected;
    const targetRow = rows.find((row) => {
      const rowMatches = !rowId
        || idMatches(row.dataset.rowId, rowId)
        || idMatches(row.dataset.programId, rowId);
      const indicatorMatches = !indicatorId || String(row.dataset.indicatorId || '') === indicatorId;

      return rowMatches && indicatorMatches;
    }) || rows.find((row) => indicatorId && String(row.dataset.indicatorId || '') === indicatorId);

    if (!targetRow) {
      return;
    }

    const coreKey = String(targetRow.dataset.coreKey || '');

    if (coreKey) {
      document.querySelectorAll('#performanceTable tbody tr').forEach((row) => {
        if (String(row.dataset.coreKey || '') === coreKey) {
          row.style.display = '';
        }
      });

      document.querySelectorAll('#performanceTable tbody tr.program-header').forEach((headerRow) => {
        if (String(headerRow.dataset.coreKey || '') !== coreKey) {
          return;
        }

        const icon = headerRow.querySelector('.program-toggle-icon');
        if (icon) {
          icon.classList.add('rotate-180');
        }
      });
    }

    targetRow.style.display = '';

    window.setTimeout(() => {
      targetRow.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
      targetRow.classList.add('dashboard-highlight-row');
    }, 50);

    window.setTimeout(() => {
      targetRow.classList.remove('dashboard-highlight-row');
    }, 5000);
  });
</script>

@include('components.financial_input_persistence')
