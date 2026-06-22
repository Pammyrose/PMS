<!-- TABS -->
<div class="flex items-center mt-4">
    <div class="flex gap-6">
        <a href="{{ route('continuing_physical') }}"
            class="font-semibold text-blue-600 border-b-2 border-blue-600 pb-2 text-decoration-none d-inline-block">
            Physical
        </a>
        <button type="button" class="text-gray-400 pb-2">
            Financial
        </button>
    </div>
</div>

<div class="d-flex justify-content-end align-items-center mt-3 mb-1">
    <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
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
