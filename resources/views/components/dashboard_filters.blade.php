<form
    method="GET"
    action="{{ route('dashboard') }}"
    class="d-flex flex-wrap align-items-center gap-3 mt-3 mt-sm-0"
    id="dashboardFilterForm"
>
    <div class="d-flex align-items-center gap-2">
        <label for="dashboard_office" class="form-label fw-semibold text-muted mb-0 fs-6">
            Office
        </label>

        <select
            id="dashboard_office"
            name="office_id"
            class="form-select form-select-md shadow-sm border-primary-subtle"
            style="width: 190px; min-width: 160px;"
            aria-label="Select dashboard office"
            onchange="this.form.submit()"
        >
            @if($officeAllowsAll ?? false)
                <option value="all" {{ ($selectedOffice ?? 'all') === 'all' ? 'selected' : '' }}>
                    {{ $officeAllLabel ?? 'All Offices' }}
                </option>
            @endif

            @foreach(($officeOptions ?? collect()) as $office)
                <option
                    value="{{ $office['id'] }}"
                    {{ (string) ($selectedOffice ?? '') === (string) $office['id'] ? 'selected' : '' }}
                >
                    {{ $office['name'] }} ({{ $office['type'] }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="d-flex align-items-center gap-2">
        <label for="dashboard_sector" class="form-label fw-semibold text-muted mb-0 fs-6">
            Sector
        </label>

        <select
            id="dashboard_sector"
            name="sector"
            class="form-select form-select-md shadow-sm border-primary-subtle"
            style="width: 170px; min-width: 140px;"
            aria-label="Select dashboard sector"
            onchange="this.form.submit()"
        >
            @php
                $currentSector = $selectedSector ?? 'all';
                $sectors = $sectorOptions ?? collect();
            @endphp

            <option value="all" {{ $currentSector === 'all' ? 'selected' : '' }}>
                All
            </option>

            @foreach($sectors as $sector)
                <option
                    value="{{ $sector['key'] }}"
                    {{ $currentSector === $sector['key'] ? 'selected' : '' }}
                >
                    {{ $sector['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="d-flex align-items-center gap-2">
        <label for="dashboard_year" class="form-label fw-semibold text-muted mb-0 fs-6">
            Year
        </label>

        <select
            id="dashboard_year"
            name="year"
            class="form-select form-select-md shadow-sm border-primary-subtle"
            style="width: 140px; min-width: 120px;"
            aria-label="Select dashboard year"
            onchange="this.form.submit()"
        >
            @php
                $currentYear = (int) ($year ?? now()->year);
                $options = $yearOptions ?? collect(range(now()->year - 5, now()->year + 1));
            @endphp

            @foreach($options as $optionYear)
                <option
                    value="{{ $optionYear }}"
                    {{ $currentYear === (int) $optionYear ? 'selected' : '' }}
                >
                    {{ $optionYear }}
                </option>
            @endforeach
        </select>
    </div>
</form>
