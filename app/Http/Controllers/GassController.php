<?php

namespace App\Http\Controllers;

use App\Models\Gass_Physical;
use App\Models\Gass_Pap;
use App\Models\Gass_Indicator;
use App\Models\Gass_Target;
use App\Models\Gass_Accomplishment;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GassController extends Controller
{
    /**
     * Display the physical accomplishments list
     * - If a specific program is provided → show only entries for that program
     * - Otherwise → show entries for all programs
     */
    public function index(Request $request, Gass_Pap $program = null)
    {
        $year = $request->query('year', 2025);
        $office_id = $request->query('office_id', 1);   // change default later if needed

        if ($program) {
            // single program view
            $programs = collect([$program]);
            $entries = Gass_Physical::where('programs_id', $program->id)
                ->where('year', $year)
                ->where('office_id', $office_id)
                ->get();
        } else {
            // GASS overview — all programs
            $programs = Gass_Pap::latest()->get();
            $entries = Gass_Physical::where('year', $year)
                ->where('office_id', $office_id)
                ->get();
        }

        // Fetch all indicators keyed by program_id
        $indicators = Gass_Indicator::get()->groupBy('program_id');

        // Prepare indicators data for JavaScript (flat structure by program_id)
        $indicatorsForJs = [];
        foreach ($indicators as $programId => $programIndicators) {
            $indicatorsForJs[$programId] = $programIndicators->map(function ($indicator) {
                $officeIds = collect($indicator->office_id ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values();

                $officeList = Office::whereIn('id', $officeIds->all())
                    ->get(['id', 'name'])
                    ->map(fn ($office) => [
                        'id' => $office->id,
                        'name' => $office->name,
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => $indicator->id,
                    'name' => $indicator->name,
                    'indicator_type' => $indicator->indicator_type,
                    'program_id' => $indicator->program_id,
                    'office_id' => $officeIds->first(),
                    'office_ids' => $officeIds->all(),
                    'office_list' => $officeList,
                ];
            })->toArray();
        }
        
        $existing = $entries->keyBy('programs_id');

        $targets = Gass_Target::where('year', $year)
            ->where('office_id', $office_id)
            ->get()
            ->keyBy('indicator_id')
            ->map(fn ($row) => $this->formatSectionRecordForJs($row))
            ->toArray();

        $accomplishments = Gass_Accomplishment::where('year', $year)
            ->where('office_id', $office_id)
            ->get()
            ->keyBy('indicator_id')
            ->map(fn ($row) => $this->formatSectionRecordForJs($row))
            ->toArray();
        
        // Get all offices organized by parent for the modal
        $offices = Office::whereNull('parent_id')->with('children')->get();

        return view('admin.gass.gass_physical', compact(
            'entries',
            'programs',
            'existing',
            'indicators',
            'indicatorsForJs',
            'targets',
            'accomplishments',
            'offices',
            'year',
            'office_id',
            'program'
        ));
    }

    /**
     * Overview page showing all programs
     */
    public function overview()
    {
        $allPrograms = Gass_Pap::with('indicator')->latest()->get();
        
        // Get all offices organized by parent with children loaded
        $offices = Office::whereNull('parent_id')->with('children')->get();
        
        // Group programs by title, program, project, and activities
        $grouped = $allPrograms->groupBy(function ($program) {
            return json_encode([
                'title' => $program->title,
                'program' => $program->program,
                'project' => $program->project,
                'activities' => $program->activities,
            ]);
        })->map(function ($group) {
            // Build a map of subactivities to their original programs with indicators
            $subactivitiesMap = [];
            
            foreach ($group as $program) {
                $subs = collect(explode("\n", trim($program->subactivities ?? '')))->filter(fn($s) => trim($s));
                foreach ($subs as $sub) {
                    $trimmedSub = trim($sub);
                    if (!isset($subactivitiesMap[$trimmedSub])) {
                        $subactivitiesMap[$trimmedSub] = [];
                    }
                    $subactivitiesMap[$trimmedSub][] = [
                        'program_id' => $program->id,
                        'indicator' => $program->indicator,
                    ];
                }
            }

            // Use the first program as the base
            $base = $group->first();
            $base->subactivities_map = $subactivitiesMap;
            $base->group_programs = $group;
            
            return $base;
        })->values();

        $programs = $grouped;
        return view('admin.gass.gass', compact('programs', 'offices'));
    }

    /**
     * Store or update physical accomplishment entries
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userId = Auth::id();
        $officeId = $request->input('office_id', 1);

        $createdCount = 0;
        $updatedCount = 0;
        $firstProgramId = null;

        foreach ($request->entries as $data) {

            // Capture the first program ID to use for redirect
            if ($firstProgramId === null) {
                $firstProgramId = $data['programs_id'] ?? null;
            }

            // Skip empty rows
            $hasMeaningfulData = collect($data)
                ->except(['programs_id', 'year', 'period_type'])
                ->filter(fn($v) => $v !== null && $v !== '' && $v !== '')
                ->isNotEmpty();

            if (!$hasMeaningfulData) {
                continue;
            }

            // Check if exists first
            $existing = Gass_Physical::where([
                'programs_id' => $data['programs_id'],
                'year' => $data['year'],
                'period_type' => $data['period_type'],
                'office_id' => $officeId,
            ])->first();

            $record = Gass_Physical::updateOrCreate(
                [
                    'programs_id' => $data['programs_id'],
                    'year' => $data['year'],
                    'period_type' => $data['period_type'],
                    'office_id' => $officeId,
                ],
                [
                    'user_id' => $userId,
                    'performance_indicator' => $data['performance_indicator'] ?? null,
                    'target' => $data['target'] ?? 0,
                    'jan' => $data['jan'] ?? 0,
                    'feb' => $data['feb'] ?? 0,
                    'mar' => $data['mar'] ?? 0,
                    'apr' => $data['apr'] ?? 0,
                    'may' => $data['may'] ?? 0,
                    'jun' => $data['jun'] ?? 0,
                    'jul' => $data['jul'] ?? 0,
                    'aug' => $data['aug'] ?? 0,
                    'sep' => $data['sep'] ?? 0,
                    'oct' => $data['oct'] ?? 0,
                    'nov' => $data['nov'] ?? 0,
                    'dec' => $data['dec'] ?? 0,
                    'q1' => $data['q1'] ?? 0,
                    'q2' => $data['q2'] ?? 0,
                    'q3' => $data['q3'] ?? 0,
                    'q4' => $data['q4'] ?? 0,
                    'first_half' => $data['first_half'] ?? 0,
                    'second_half' => $data['second_half'] ?? 0,
                    'annual_total' => $data['annual_total'] ?? 0,
                    'remarks' => $data['remarks'] ?? null,
                ]
            );

            if ($existing) {
                $updatedCount++;
            } else {
                $createdCount++;
            }
        }

        // Redirect back to the physical page with the program ID if available
        $redirectRoute = $firstProgramId 
            ? route('admin.gass.physical', $firstProgramId)
            : route('admin.gass.physical');

        return redirect($redirectRoute)
            ->with(
                'success',
                "$createdCount created, $updatedCount updated successfully."
            );
    }
           

    private function getValidationRules()
    {
        return [
            'entries' => 'required|array',
        ];
    }


    public function indicatorsIndex(Request $request)
    {
        $indicators = Gass_Indicator::latest()->get();

        return view('admin.gass.indicators', compact('indicators'));
    }

    /**
     * Show form to create new indicator
     */
    public function createIndicator()
    {
        return view('admin.gass.indicator_create');
    }

    /**
     * Store new indicator
     */
    public function storeIndicator(Request $request)
    {
        $baseValidated = $request->validate([
            'indicator_name' => 'required|string|max:255',
            'indicator_type' => 'required|string|in:non-comulative,comulative,semi-comulative',
            'program_id' => 'required|exists:gass_pap,id',
            'office_id' => 'required|array|min:1',
            'office_id.*' => 'required|exists:offices,id',
        ]);

        $indicatorName = trim($baseValidated['indicator_name']);

        $officeIds = collect($baseValidated['office_id'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $indicator = Gass_Indicator::firstOrNew([
            'program_id' => $baseValidated['program_id'],
            'name' => $indicatorName,
        ]);

        $wasNew = !$indicator->exists;

        $indicator->fill([
            'user_id' => Auth::id(),
            'name' => $indicatorName,
            'indicator_type' => $baseValidated['indicator_type'],
            'office_id' => $officeIds,
        ]);
        $indicator->save();

        $createdCount = $wasNew ? 1 : 0;
        $updatedCount = $wasNew ? 0 : 1;
        $message = "$createdCount created, $updatedCount updated successfully.";

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => $message,
                'success' => true,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'indicator' => $indicator
            ]);
        }

        return redirect()->route('gass_physical')
            ->with('success', $message);
    }

    /**
     * Update indicator
     */
    public function editProgram(Gass_Pap $program)
    {
        return view('admin.gass.program_edit', compact('program'));
    }

public function update(Request $request, Gass_Indicator $indicator)
{
    $validated = $request->validate([
        'indicator_name' => 'nullable|string|max:255',
        'indicator_type' => 'nullable|string|in:non-comulative,comulative,semi-comulative',
        'office_id' => 'nullable|array|min:1',
        'office_id.*' => 'required|exists:offices,id',
    ]);

    $newName = isset($validated['indicator_name']) ? trim($validated['indicator_name']) : null;
    $nameChanged = $newName !== null && $newName !== '' && $newName !== $indicator->name;

    if ($nameChanged) {
        $newIndicator = new Gass_Indicator();
        $newIndicator->program_id = $indicator->program_id;
        $newIndicator->user_id = Auth::id();
        $newIndicator->name = $newName;
        if (isset($validated['indicator_type'])) {
            $newIndicator->indicator_type = $validated['indicator_type'];
        } else {
            $newIndicator->indicator_type = $indicator->indicator_type;
        }
        $newIndicator->office_id = isset($validated['office_id'])
            ? collect($validated['office_id'])->map(fn ($id) => (int) $id)->unique()->values()->all()
            : ($indicator->office_id ?? []);
        $newIndicator->save();

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => 'Indicator changed. A new indicator ID was created.',
                'success' => true,
                'indicator' => $newIndicator,
                'created_new' => true,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Indicator changed. A new indicator ID was created.');
    }

    $updateData = [];
    if ($newName !== null && $newName !== '') {
        $updateData['name'] = $newName;
    }
    if (isset($validated['indicator_type'])) {
        $updateData['indicator_type'] = $validated['indicator_type'];
    }
    if (isset($validated['office_id'])) {
        $updateData['office_id'] = collect($validated['office_id'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    $indicator->update($updateData);

    if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
        return response()->json([
            'message' => 'Indicator updated successfully.',
            'success' => true,
            'indicator' => $indicator
        ]);
    }

    return redirect()
        ->back()
        ->with('success', 'Indicator updated successfully.');
}

public function destroyIndicator(Request $request, Gass_Indicator $indicator)
{
    $indicator->delete();

    if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
        return response()->json([
            'message' => 'Indicator deleted successfully.',
            'success' => true,
        ]);
    }

    return redirect()
        ->back()
        ->with('success', 'Indicator deleted successfully.');
}

public function storeTargets(Request $request)
{
    $validated = $request->validate([
        'entries' => 'required|array',
        'entries.*.program_id' => 'required|exists:gass_pap,id',
        'entries.*.indicator_id' => 'required|exists:gass_indicators,id',
        'entries.*.year' => 'required|integer|min:2000|max:2100',
        'entries.*.office_id' => 'nullable|exists:offices,id',
        'entries.*.jan' => 'nullable|numeric|min:0',
        'entries.*.feb' => 'nullable|numeric|min:0',
        'entries.*.mar' => 'nullable|numeric|min:0',
        'entries.*.q1' => 'nullable|numeric|min:0',
        'entries.*.apr' => 'nullable|numeric|min:0',
        'entries.*.may' => 'nullable|numeric|min:0',
        'entries.*.jun' => 'nullable|numeric|min:0',
        'entries.*.q2' => 'nullable|numeric|min:0',
        'entries.*.jul' => 'nullable|numeric|min:0',
        'entries.*.aug' => 'nullable|numeric|min:0',
        'entries.*.sep' => 'nullable|numeric|min:0',
        'entries.*.q3' => 'nullable|numeric|min:0',
        'entries.*.oct' => 'nullable|numeric|min:0',
        'entries.*.nov' => 'nullable|numeric|min:0',
        'entries.*.dec' => 'nullable|numeric|min:0',
        'entries.*.q4' => 'nullable|numeric|min:0',
        'entries.*.annual_total' => 'nullable|numeric|min:0',
    ]);

    $userId = Auth::id();
    $createdCount = 0;
    $updatedCount = 0;

    foreach ($validated['entries'] as $entry) {
        $record = Gass_Target::firstOrNew([
            'indicator_id' => $entry['indicator_id'],
            'year' => $entry['year'],
            'office_id' => $entry['office_id'] ?? null,
        ]);

        $wasExisting = $record->exists;

        $record->fill([
            'user_id' => $userId,
            'program_id' => $entry['program_id'],
            'jan' => $entry['jan'] ?? 0,
            'feb' => $entry['feb'] ?? 0,
            'mar' => $entry['mar'] ?? 0,
            'q1' => $entry['q1'] ?? 0,
            'apr' => $entry['apr'] ?? 0,
            'may' => $entry['may'] ?? 0,
            'jun' => $entry['jun'] ?? 0,
            'q2' => $entry['q2'] ?? 0,
            'jul' => $entry['jul'] ?? 0,
            'aug' => $entry['aug'] ?? 0,
            'sep' => $entry['sep'] ?? 0,
            'q3' => $entry['q3'] ?? 0,
            'oct' => $entry['oct'] ?? 0,
            'nov' => $entry['nov'] ?? 0,
            'dec' => $entry['dec'] ?? 0,
            'q4' => $entry['q4'] ?? 0,
            'annual_total' => $entry['annual_total'] ?? 0,
        ]);

        $record->save();

        if ($wasExisting) {
            $updatedCount++;
        } else {
            $createdCount++;
        }
    }

    return response()->json([
        'success' => true,
        'message' => "$createdCount created, $updatedCount updated successfully.",
        'created_count' => $createdCount,
        'updated_count' => $updatedCount,
    ]);
}

public function storeAccomplishments(Request $request)
{
    $validated = $request->validate([
        'entries' => 'required|array',
        'entries.*.program_id' => 'required|exists:gass_pap,id',
        'entries.*.indicator_id' => 'required|exists:gass_indicators,id',
        'entries.*.year' => 'required|integer|min:2000|max:2100',
        'entries.*.office_id' => 'nullable|exists:offices,id',
        'entries.*.jan' => 'nullable|numeric|min:0',
        'entries.*.feb' => 'nullable|numeric|min:0',
        'entries.*.mar' => 'nullable|numeric|min:0',
        'entries.*.q1' => 'nullable|numeric|min:0',
        'entries.*.apr' => 'nullable|numeric|min:0',
        'entries.*.may' => 'nullable|numeric|min:0',
        'entries.*.jun' => 'nullable|numeric|min:0',
        'entries.*.q2' => 'nullable|numeric|min:0',
        'entries.*.jul' => 'nullable|numeric|min:0',
        'entries.*.aug' => 'nullable|numeric|min:0',
        'entries.*.sep' => 'nullable|numeric|min:0',
        'entries.*.q3' => 'nullable|numeric|min:0',
        'entries.*.oct' => 'nullable|numeric|min:0',
        'entries.*.nov' => 'nullable|numeric|min:0',
        'entries.*.dec' => 'nullable|numeric|min:0',
        'entries.*.q4' => 'nullable|numeric|min:0',
        'entries.*.annual_total' => 'nullable|numeric|min:0',
    ]);

    $userId = Auth::id();
    $createdCount = 0;
    $updatedCount = 0;

    foreach ($validated['entries'] as $entry) {
        $record = Gass_Accomplishment::firstOrNew([
            'indicator_id' => $entry['indicator_id'],
            'year' => $entry['year'],
            'office_id' => $entry['office_id'] ?? null,
        ]);

        $wasExisting = $record->exists;

        $record->fill([
            'user_id' => $userId,
            'program_id' => $entry['program_id'],
            'jan' => $entry['jan'] ?? 0,
            'feb' => $entry['feb'] ?? 0,
            'mar' => $entry['mar'] ?? 0,
            'q1' => $entry['q1'] ?? 0,
            'apr' => $entry['apr'] ?? 0,
            'may' => $entry['may'] ?? 0,
            'jun' => $entry['jun'] ?? 0,
            'q2' => $entry['q2'] ?? 0,
            'jul' => $entry['jul'] ?? 0,
            'aug' => $entry['aug'] ?? 0,
            'sep' => $entry['sep'] ?? 0,
            'q3' => $entry['q3'] ?? 0,
            'oct' => $entry['oct'] ?? 0,
            'nov' => $entry['nov'] ?? 0,
            'dec' => $entry['dec'] ?? 0,
            'q4' => $entry['q4'] ?? 0,
            'annual_total' => $entry['annual_total'] ?? 0,
        ]);

        $record->save();

        if ($wasExisting) {
            $updatedCount++;
        } else {
            $createdCount++;
        }
    }

    return response()->json([
        'success' => true,
        'message' => "$createdCount created, $updatedCount updated successfully.",
        'created_count' => $createdCount,
        'updated_count' => $updatedCount,
    ]);
}

private function formatSectionRecordForJs($row): array
{
    return [
        'jan' => (float) ($row->jan ?? 0),
        'feb' => (float) ($row->feb ?? 0),
        'mar' => (float) ($row->mar ?? 0),
        'q1' => (float) ($row->q1 ?? 0),
        'apr' => (float) ($row->apr ?? 0),
        'may' => (float) ($row->may ?? 0),
        'jun' => (float) ($row->jun ?? 0),
        'q2' => (float) ($row->q2 ?? 0),
        'jul' => (float) ($row->jul ?? 0),
        'aug' => (float) ($row->aug ?? 0),
        'sep' => (float) ($row->sep ?? 0),
        'q3' => (float) ($row->q3 ?? 0),
        'oct' => (float) ($row->oct ?? 0),
        'nov' => (float) ($row->nov ?? 0),
        'dec' => (float) ($row->dec ?? 0),
        'q4' => (float) ($row->q4 ?? 0),
        'annual_total' => (float) ($row->annual_total ?? 0),
    ];
}
}