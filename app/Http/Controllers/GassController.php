<?php

namespace App\Http\Controllers;

use App\Models\Physical;
use App\Models\Program;
use App\Models\Indicator;
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
    public function index(Request $request, Program $program)
    {
        $year = $request->query('year', 2025);
        $office_id = $request->query('office_id', 1);   // change default later if needed

        if ($program) {
            // single program view
            $programs = collect([$program]);
            $entries = Physical::where('programs_id', $program->id)
                ->where('year', $year)
                ->where('office_id', $office_id)
                ->get();
        } else {
            // GASS overview — all programs
            $programs = Program::latest()->get();
            $entries = Physical::where('year', $year)
                ->where('office_id', $office_id)
                ->get();
        }

        // Fetch all indicators and key them by program_id
        $indicators = Indicator::all()->keyBy('program_id');
        $existing = $entries->keyBy('programs_id');

        return view('admin.gass.gass_physical', compact(
            'entries',
            'programs',
            'existing',
            'indicators',
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
        $allPrograms = Program::with('indicator.office')->latest()->get();
        
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
            $existing = Physical::where([
                'programs_id' => $data['programs_id'],
                'year' => $data['year'],
                'period_type' => $data['period_type'],
                'office_id' => $officeId,
            ])->first();

            $record = Physical::updateOrCreate(
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
        $indicators = Indicator::latest()->get();

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
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'program_id' => 'nullable|exists:programs,id',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['office_id'] = $request->input('office_id');

        $indicator = Indicator::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Indicator created successfully.',
                'indicator' => $indicator
            ]);
        }

        return redirect()->route('admin.gass.indicators')
            ->with('success', 'Indicator created successfully.');
    }

    /**
     * Update indicator
     */
    public function editProgram(Program $program)
    {
        return view('admin.gass.program_edit', compact('program'));
    }

public function update(Request $request, Indicator $indicator)
{
    $validated = $request->validate([
        'name'     => 'nullable|string|max:255',
        'target'   => 'nullable|string|max:255',
        'deadline' => 'nullable|date',
        'office_id' => 'nullable|exists:offices,id',
    ]);

    $validated['office_id'] = $request->input('office_id');
    $indicator->update($validated);

    if ($request->wantsJson()) {
        return response()->json([
            'message' => 'Indicator updated successfully.',
            'indicator' => $indicator
        ]);
    }

    return redirect()
        ->back()
        ->with('success', 'Indicator updated successfully.');
}
}