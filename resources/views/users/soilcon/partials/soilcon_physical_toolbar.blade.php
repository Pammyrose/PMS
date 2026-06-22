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
                    class="form-control form-control pe-5 ps-4 shadow-sm" placeholder="Search..."
                    value="{{ old('search', $search ?? '') }}" autocomplete="off"
                    aria-label="Search programs, projects and activities" required>
                <!-- Search icon inside input (very common pattern) -->
                <span class="position-absolute top-50 end-0 translate-middle-y pe-3 text-muted">
                    <i class="bi bi-search"></i>
                </span>
            </div>

        </form>
    </div>

    <!-- Right side -->
    <div class="flex items-center gap-2 ">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                id="columnOptionsDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="fa fa-table-columns me-1"></i> Columns
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="columnOptionsDropdown">
                <li class="dropend">
                    <button class="dropdown-item dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa fa-bullseye me-1"></i> Target
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button onclick="toggleTargetColumns()" class="dropdown-item" id="targetBtn" type="button">
                                <i class="fa fa-plus me-1"></i> Targets
                            </button>
                        </li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleMonthInputs()" class="dropdown-item" id="monthBtn" type="button"
                        disabled style="display:none;">
                        <i class="fa fa-calendar-days me-1"></i> Show Months
                    </button>
                </li>
                <li class="dropend">
                    <button class="dropdown-item dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa fa-list-check me-1"></i> Accomplishment
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button onclick="toggleAccompColumns()" class="dropdown-item" id="accompBtn" type="button">
                                <i class="fa fa-plus me-1"></i> Accomplishments
                            </button>
                        </li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSummaryColumns()" class="dropdown-item" id="summaryBtn" type="button">
                        <i class="fa fa-chart-bar me-1"></i> Summary
                    </button>
                </li>
                <li>
                    <button onclick="togglePendingColumns()" class="dropdown-item" id="pendingBtn" type="button">
                        <i class="fa fa-hourglass-half me-1"></i> Pending
                    </button>
                </li>
                <li>
                    <button onclick="toggleRemarksColumn()" class="dropdown-item" id="remarksBtn" type="button">
                        <i class="fa fa-plus me-1"></i> Remarks
                    </button>
                </li>
            </ul>
        </div>
        <button onclick="saveAllSectionEntries()" class="btn btn-primary btn-sm" id="saveAllBtn">
            <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
    </div>
</div>
