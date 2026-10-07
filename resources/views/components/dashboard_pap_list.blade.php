@php
            $dashboardPapList = collect($papList ?? []);
            $dashboardPapGroups = $dashboardPapList
              ->groupBy(fn ($pap) => (string) ($pap['sector'] ?: 'N/A'))
              ->sortKeys();
          @endphp

          @if($dashboardPapList->isEmpty())
            <div class="p-5 text-center text-muted">
              <i class="fa-solid fa-folder-open fa-2x mb-3 text-success"></i>
              <p class="mb-0 fw-semibold">No PAP records found for this dashboard selection.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4" style="width: 70px;">#</th>
                    <th>PAP</th>
                    <th style="width: 220px;">Office</th>
                    <th style="width: 130px;">Sector</th>
                    <th style="width: 120px;">Year</th>
                    <th style="width: 150px;">Indicators</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $papRowNumber = 0;
                  @endphp
                  @foreach($dashboardPapGroups as $sector => $sectorPaps)
                    @php
                      $papSectorGroupId = 'pap-sector-group-' . $loop->index;
                      $sectorIndicatorCount = $sectorPaps->sum(fn ($pap) => (int) ($pap['indicator_count'] ?? 0));
                    @endphp
                    <tr class="pap-sector-heading">
                      <td colspan="6" class="p-0">
                        <button
                          type="button"
                          class="pap-sector-toggle"
                          data-pap-sector-toggle="{{ $papSectorGroupId }}"
                          aria-expanded="false"
                          aria-controls="{{ $papSectorGroupId }}"
                        >
                          <span class="d-flex align-items-center gap-3">
                            <span class="pap-sector-chevron" aria-hidden="true">
                              <i class="fa-solid fa-chevron-right"></i>
                            </span>
                            <span class="badge text-bg-success">{{ $sector }}</span>
                          </span>
                          <span class="pap-sector-summary">
                            {{ number_format($sectorPaps->count()) }} PAP{{ $sectorPaps->count() === 1 ? '' : 's' }}
                            <span aria-hidden="true">&bull;</span>
                            {{ number_format($sectorIndicatorCount) }} indicator{{ $sectorIndicatorCount === 1 ? '' : 's' }}
                          </span>
                        </button>
                      </td>
                    </tr>

                    @foreach($sectorPaps as $pap)
                      @php($papRowNumber++)
                      <tr
                        @if($loop->first) id="{{ $papSectorGroupId }}" @endif
                        class="dashboard-link-row pap-sector-row d-none"
                        data-pap-sector-row="{{ $papSectorGroupId }}"
                        data-href="{{ $pap['url'] ?? '#' }}"
                        tabindex="0"
                        role="link"
                        aria-label="Open {{ $pap['name'] ?: 'Untitled PAP' }}"
                      >
                        <td class="ps-4 text-muted fw-semibold">{{ $papRowNumber }}</td>
                        <td class="fw-semibold">
                          <a href="{{ $pap['url'] ?? '#' }}" class="text-gray-900 text-decoration-none">
                            {{ $pap['name'] ?: 'Untitled PAP' }}
                          </a>
                        </td>
                        <td class="text-muted small">{{ !empty($pap['offices']) ? implode(', ', $pap['offices']) : 'N/A' }}</td>
                        <td>
                          <span class="badge text-bg-success">{{ $pap['sector'] ?: 'N/A' }}</span>
                        </td>
                        <td>{{ $pap['year'] ?: 'N/A' }}</td>
                        <td>{{ number_format((int) ($pap['indicator_count'] ?? 0)) }}</td>
                      </tr>
                    @endforeach
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
