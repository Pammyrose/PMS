@props(['sector'])

<a href="{{ route('wfp.export', ['sector' => $sector, 'year' => request()->integer('year') ?: now()->year]) }}"
    class="btn btn-outline-success btn-sm"
    title="Download the saved WFP data using the official Excel layout">
    <i class="fa fa-file-arrow-down me-1"></i> Generate Excel
</a>
