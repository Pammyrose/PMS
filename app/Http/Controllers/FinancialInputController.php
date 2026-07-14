<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccomplishment;
use App\Models\FinancialTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FinancialInputController extends Controller
{
    private const SECTOR_TYPE_CODES = [
        'gass' => 'GASS',
        'sto' => 'STO',
        'enf' => 'ENF',
        'pa' => 'PA',
        'engp' => 'ENGP',
        'lands' => 'Lands',
        'soilcon' => 'Soilcon',
        'nra' => 'NRA',
        'paria' => 'PARIA',
        'cobb' => 'COBB',
        'continuing' => 'CONTINUING',
    ];

    private const PERIODS = [
        'jan', 'feb', 'mar', 'q1',
        'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3',
        'oct', 'nov', 'dec', 'q4',
        'annual_total',
    ];

    public function store(Request $request, string $sector): JsonResponse
    {
        $sector = strtolower(trim($sector));
        abort_unless(array_key_exists($sector, self::SECTOR_TYPE_CODES), 404);

        $typeId = DB::table('types')
            ->where('code', self::SECTOR_TYPE_CODES[$sector])
            ->value('id');

        abort_if(! $typeId, 422, 'The selected financial section is unavailable.');

        $ppaRule = Rule::exists('ppa', 'id')->where(
            fn ($query) => $query->where('types_id', $typeId)
        );

        $rules = [
            'entries' => 'required|array|min:1',
            'entries.*.program_id' => ['required', 'integer', $ppaRule],
            'entries.*.row_id' => ['required', 'integer', $ppaRule],
            'entries.*.indicator_id' => ['required', 'integer', 'exists:indicators,id'],
            'entries.*.office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'entries.*.year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'entries.*.kind' => ['nullable', Rule::in(['target', 'accomplishment'])],
            'entries.*.car_totals' => ['nullable', 'array'],
            'entries.*.group_totals' => ['nullable', 'array'],
        ];

        foreach (self::PERIODS as $period) {
            $rules["entries.*.$period"] = ['nullable', 'numeric', 'min:0'];
        }

        $entries = $request->validate($rules)['entries'];
        $user = $request->user();
        $userOfficeId = (int) ($user?->office_id ?? 0);

        if ($this->shouldScopeToUserOffice()) {
            foreach ($entries as $entry) {
                abort_if((int) ($entry['office_id'] ?? 0) !== $userOfficeId, 403);
            }
        }

        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($entries, $sector, &$createdCount, &$updatedCount) {
            foreach ($entries as $entry) {
                $kind = (string) ($entry['kind'] ?? 'target');
                $identity = [
                    'sector' => $sector,
                    'year' => (int) $entry['year'],
                    'office_id' => filled($entry['office_id'] ?? null) ? (int) $entry['office_id'] : null,
                    'row_id' => (int) $entry['row_id'],
                    'indicator_id' => (int) $entry['indicator_id'],
                ];

                $modelClass = $kind === 'accomplishment'
                    ? FinancialAccomplishment::class
                    : FinancialTarget::class;
                $record = $modelClass::firstOrNew($identity);
                $wasExisting = $record->exists;
                $values = [
                    'user_id' => Auth::id(),
                    'program_id' => (int) $entry['program_id'],
                    'car_totals' => $entry['car_totals'] ?? [],
                    'group_totals' => $entry['group_totals'] ?? [],
                ];

                foreach (self::PERIODS as $period) {
                    $values[$period] = $entry[$period] ?? 0;
                }

                $record->fill($values)->save();
                $wasExisting ? $updatedCount++ : $createdCount++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "$createdCount created, $updatedCount updated successfully.",
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
        ]);
    }
}
