@php
    $defaultOfficeUnits = [
        ['name' => 'RO', 'province' => false],
        ['name' => 'ABRA', 'province' => true],
        ['name' => 'APAYAO', 'province' => true],
        ['name' => 'BENGUET', 'province' => true],
        ['name' => 'IFUGAO', 'province' => true],
        ['name' => 'KALINGA', 'province' => true],
        ['name' => 'MT.PROVINCE', 'province' => true],
    ];
@endphp

<tr class="data-row default-office-unit-row"
    data-core-key="{{ $programCoreKey }}"
    data-search-text="{{ e(($programSearchText ?? '') . ' CAR RO ABRA APAYAO BENGUET IFUGAO KALINGA MT.PROVINCE') }}"
    style="display:none;">
    <td class="px-4 py-3" aria-hidden="true"></td>
    <td class="px-4 py-3 small text-center">
        <div class="office-lines">
            <div class="office-line car-office-line" data-default-office-unit="CAR">CAR</div>
            @foreach($defaultOfficeUnits as $officeUnit)
                <div class="office-line {{ $officeUnit['province'] ? 'group-total-office-line' : '' }}"
                    data-default-office-unit="{{ $officeUnit['name'] }}">
                    {{ $officeUnit['name'] }}
                </div>
            @endforeach
        </div>
    </td>
</tr>
