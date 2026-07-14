<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100" style="border-top: 4px solid #0d6efd !important;">
    @php
      $sectorRows = collect($fieldStats ?? []);
      $sectorCutoffIndex = $sectorRows->search(fn ($field) => strtolower((string) ($field['key'] ?? '')) === 'pa' || strtoupper((string) ($field['label'] ?? '')) === 'PA');
      $sectorVisibleCount = $sectorCutoffIndex === false ? 4 : ((int) $sectorCutoffIndex + 1);
    @endphp
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-3" style="background: #eff6ff;">
      <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3 mb-0">
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white bg-primary" style="width: 34px; height: 34px;">
          <i class="fa-solid fa-chart-bar"></i>
        </span>
        Sectors Performance Ranking
      </h3>
      <span class="badge rounded-pill text-primary bg-white border border-primary-subtle">Sector</span>
    </div>

    <div class="p-6" data-ranking-list="sectors">
      @forelse($sectorRows as $field)
        @php
          $progress = (float) ($field['progress'] ?? 0);
          $isOnTrack = $progress >= 80;
          $isNeedsAttention = $progress >= 60 && $progress < 80;
          $textClass = $isOnTrack ? 'text-emerald-600' : ($isNeedsAttention ? 'text-amber-600' : 'text-red-600');
          $barClass = $isOnTrack
            ? 'bg-gradient-to-r from-emerald-400 to-emerald-600'
            : ($isNeedsAttention
                ? 'bg-gradient-to-r from-amber-400 to-amber-500'
                : 'bg-gradient-to-r from-red-400 to-red-500');
          $isHiddenRankItem = $loop->index >= $sectorVisibleCount;
        @endphp
        <a
          href="{{ route($field['key'] . '_physical') }}"
          class="block space-y-2 rounded-xl p-3 -m-3 text-decoration-none hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition {{ $isHiddenRankItem ? 'd-none' : '' }}"
          data-ranking-extra="{{ $isHiddenRankItem ? 'true' : 'false' }}"
          aria-label="Open {{ $field['label'] }} sector page"
        >
          <div class="flex justify-between items-center text-sm font-medium gap-3">
            <span class="text-gray-800">{{ $field['label'] }}</span>
            <span class="d-flex align-items-center gap-2 {{ $textClass }}">
              {{ number_format($progress, 0) }}%
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </span>
          </div>
          <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full {{ $barClass }} rounded-full" style="width: {{ $progress }}%"></div>
          </div>
          <div class="flex justify-between gap-3 text-xs text-gray-500">
            <span>Target: {{ number_format((float) ($field['target_total'] ?? 0), 0) }} | Accomplished: {{ number_format((float) ($field['accomp_total'] ?? 0), 0) }}</span>
            <span class="font-medium {{ $textClass }}">{{ $field['status'] }}</span>
          </div>
        </a>
      @empty
        <div class="text-sm text-gray-500">No field data available yet.</div>
      @endforelse

      @if($sectorRows->count() > $sectorVisibleCount)
        <div class="pt-2 text-center">
          <button type="button" class="btn btn-outline-primary btn-sm px-4" data-ranking-toggle="sectors">See more</button>
        </div>
      @endif
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100" style="border-top: 4px solid #198754 !important;">
    @php
      $officeRows = collect($officeStats ?? [])
        ->sortBy([
          ['accomp_total', 'desc'],
          ['label', 'asc'],
        ])
        ->values();
      $maxOfficeAccomplished = (float) $officeRows->max('accomp_total');
      $banguedIndex = $officeRows->search(fn ($office) => strtoupper((string) ($office['label'] ?? '')) === 'BANGUED');
      $officeVisibleCount = $banguedIndex === false ? 4 : ((int) $banguedIndex + 1);
    @endphp
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-3" style="background: #ecfdf5;">
      <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3 mb-0">
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white bg-success" style="width: 34px; height: 34px;">
          <i class="fa-solid fa-building"></i>
        </span>
        Office Performance Ranking
      </h3>
      <span class="badge rounded-pill text-success bg-white border border-success-subtle">Office</span>
    </div>

    <div class="p-6 d-flex flex-column gap-3" data-ranking-list="offices">
      @forelse($officeRows as $office)
        @php
          $accomplished = (float) ($office['accomp_total'] ?? 0);
          $accomplishmentShare = $maxOfficeAccomplished > 0
            ? round(($accomplished / $maxOfficeAccomplished) * 100, 2)
            : 0;
          $isHiddenRankItem = $loop->index >= $officeVisibleCount;
        @endphp
        <div
          class="space-y-2 rounded-xl p-3 -m-3 {{ $isHiddenRankItem ? 'd-none' : '' }}"
          data-ranking-extra="{{ $isHiddenRankItem ? 'true' : 'false' }}"
        >
          <div class="flex justify-between items-center text-sm font-medium gap-3">
            <span class="text-gray-800">{{ $office['label'] }}</span>
            <span class="text-emerald-600">{{ number_format($accomplished, 0) }}</span>
          </div>
          <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full" style="width: {{ $accomplishmentShare }}%"></div>
          </div>
        </div>
      @empty
        <div class="text-sm text-gray-500">No office data available yet.</div>
      @endforelse

      @if($officeRows->count() > $officeVisibleCount)
        <div class="pt-2 text-center">
          <button type="button" class="btn btn-outline-success btn-sm px-4" data-ranking-toggle="offices">See more</button>
        </div>
      @endif
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('[data-ranking-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const list = document.querySelector(`[data-ranking-list="${button.dataset.rankingToggle}"]`);
      if (!list) return;

      const extraItems = list.querySelectorAll('[data-ranking-extra="true"]');
      const isExpanded = button.dataset.expanded === 'true';

      extraItems.forEach((item) => item.classList.toggle('d-none', isExpanded));
      button.dataset.expanded = isExpanded ? 'false' : 'true';
      button.textContent = isExpanded ? 'See more' : 'See less';
    });
  });
</script>
