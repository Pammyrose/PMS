@php
            $dashboardIndicatorList = collect($indicatorList ?? []);
            $dashboardIndicatorGroups = $dashboardIndicatorList
              ->groupBy(fn ($indicator) => (string) ($indicator['sector'] ?: 'N/A'))
              ->sortKeys();
          @endphp

          @if($dashboardIndicatorList->isEmpty())
            <div class="p-5 text-center text-muted">
              <i class="fa-solid fa-list-check fa-2x mb-3 text-info"></i>
              <p class="mb-0 fw-semibold">No indicator records found for this dashboard selection.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4" style="width: 70px;">#</th>
                    <th>Indicator</th>
                    <th>PAP</th>
                    <th style="width: 220px;">Office</th>
                    <th style="width: 130px;">Sector</th>
                    <th style="width: 120px;">Year</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $indicatorRowNumber = 0;
                  @endphp
                  @foreach($dashboardIndicatorGroups as $sector => $sectorIndicators)
                    @php
                      $indicatorSectorGroupId = 'indicator-sector-group-' . $loop->index;
                      $sectorPapCount = $sectorIndicators
                        ->pluck('pap_name')
                        ->filter()
                        ->unique()
                        ->count();
                    @endphp
                    <tr class="indicator-sector-heading">
                      <td colspan="6" class="p-0">
                        <button
                          type="button"
                          class="indicator-sector-toggle"
                          data-indicator-sector-toggle="{{ $indicatorSectorGroupId }}"
                          aria-expanded="false"
                          aria-controls="{{ $indicatorSectorGroupId }}"
                        >
                          <span class="d-flex align-items-center gap-3">
                            <span class="indicator-sector-chevron" aria-hidden="true">
                              <i class="fa-solid fa-chevron-right"></i>
                            </span>
                            <span class="badge text-bg-info">{{ $sector }}</span>
                          </span>
                          <span class="indicator-sector-summary">
                            {{ number_format($sectorIndicators->count()) }} indicator{{ $sectorIndicators->count() === 1 ? '' : 's' }}
                            <span aria-hidden="true">&bull;</span>
                            {{ number_format($sectorPapCount) }} PAP{{ $sectorPapCount === 1 ? '' : 's' }}
                          </span>
                        </button>
                      </td>
                    </tr>

                    @foreach($sectorIndicators as $indicator)
                      @php
                        $indicatorRowNumber++;
                      @endphp
                      <tr
                        @if($loop->first) id="{{ $indicatorSectorGroupId }}" @endif
                        class="dashboard-link-row indicator-sector-row d-none"
                        data-indicator-sector-row="{{ $indicatorSectorGroupId }}"
                        data-href="{{ $indicator['url'] ?? '#' }}"
                        tabindex="0"
                        role="link"
                        aria-label="Open {{ $indicator['name'] ?: 'Untitled Indicator' }}"
                      >
                        <td class="ps-4 text-muted fw-semibold">{{ $indicatorRowNumber }}</td>
                        <td class="fw-semibold">
                          <a href="{{ $indicator['url'] ?? '#' }}" class="text-gray-900 text-decoration-none">
                            {{ $indicator['name'] ?: 'Untitled Indicator' }}
                          </a>
                        </td>
                        <td class="text-muted small">{{ $indicator['pap_name'] ?: 'Untitled PAP' }}</td>
                        <td class="text-muted small">{{ !empty($indicator['offices']) ? implode(', ', $indicator['offices']) : 'N/A' }}</td>
                        <td>
                          <span class="badge text-bg-info">{{ $indicator['sector'] ?: 'N/A' }}</span>
                        </td>
                        <td>{{ $indicator['year'] ?: 'N/A' }}</td>
                      </tr>
                    @endforeach
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
