<div class="flex items-center justify-between mt-2">
    <!-- Left side -->
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
            data-bs-target="#addIndicatorModal">
            <i class="fa fa-plus me-1"></i> Add PAP
        </button>

        <form method="GET" action="{{ url()->current() }}"
            class="d-flex align-items-center gap-2 flex-wrap" id="papSearchForm" role="search">
            {{-- Preserve important filters --}}
            @if(request()->filled('year'))
                <input type="hidden" name="year" value="{{ $year ?? now()->year }}">
            @endif
            @if(request()->filled('office_id'))
                <input type="hidden" name="office_id" value="{{ request('office_id') }}">
            @endif

            <div class="position-relative flex-grow-1" style="min-width: 320px; max-width: 480px;">
                <input type="search" name="search" id="papSearchInput"
                    class="form-control form-control pe-5 ps-4 shadow-sm" placeholder="Search…"
                    value="{{ old('search', $search ?? '') }}" autocomplete="off"
                    aria-label="Search programs, projects and activities" required>
                <span class="position-absolute top-50 end-0 translate-middle-y pe-3 text-muted">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </form>
    </div>

    <!-- Right side -->
    <div class="flex items-center gap-2 ">
        <button onclick="toggleTargetColumns()" class="btn btn-danger btn-sm" id="targetBtn">
            <i class="fa fa-plus me-1"></i> Targets
        </button>
        <button onclick="toggleMonthInputs()" class="btn btn-outline-secondary btn-sm" id="monthBtn"
            disabled style="display:none;">
            <i class="fa fa-calendar-days me-1"></i> Months
        </button>
        <button onclick="toggleAccompColumns()" class="btn btn-success btn-sm" id="accompBtn">
            <i class="fa fa-plus me-1"></i> Accomplishments
        </button>
        <button onclick="toggleRemarksColumn()" class="btn btn-warning btn-sm" id="remarksBtn"
            style="display:none;">
            <i class="fa fa-plus me-1"></i> Remarks
        </button>
        <button onclick="toggleSummaryColumns()" class="btn btn-info btn-sm" id="summaryBtn">
            <i class="fa fa-chart-bar me-1"></i> Summary
        </button>
        <button onclick="saveAllSectionEntries()" class="btn btn-primary btn-sm" id="saveAllBtn">
            <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
    </div>
</div>
