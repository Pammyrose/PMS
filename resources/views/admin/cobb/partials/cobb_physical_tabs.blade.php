<div class="d-flex justify-content-end align-items-center mt-3 mb-1">
    <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2" id="yearFilterForm">
        @if(request()->filled('office_id'))
            <input type="hidden" name="office_id" value="{{ request('office_id') }}">
        @endif
        <label for="year_filter" class="form-label fw-semibold text-muted mb-0 small">Year</label>
        <select id="year_filter" name="year"
            class="form-select form-select-sm shadow-sm border-primary-subtle" style="width: 110px;"
            onchange="this.form.submit()">
            @php
                $selectedYear = (int) ($year ?? now()->year);
                $yearRangeOptions = $yearOptions ?? collect(range(now()->year + 1, 2020))->values();
            @endphp
            @foreach($yearRangeOptions as $optionYear)
                <option value="{{ $optionYear }}" {{ $selectedYear === (int) $optionYear ? 'selected' : '' }}>
                    {{ $optionYear }}
                </option>
            @endforeach
        </select>
    </form>
</div>
