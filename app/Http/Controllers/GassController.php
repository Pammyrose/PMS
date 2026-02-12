<?php

namespace App\Http\Controllers;

use App\Models\Physical;
use App\Models\Program;
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
public function index(Request $request, Program $program )
{
    $year      = $request->query('year', 2025);
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

    $existing = $entries->keyBy('programs_id');

    return view('admin.gass.gass_physical', compact(
        'entries', 'programs', 'existing', 'year', 'office_id', 'program'
    ));
}

    /**
     * Overview page showing all programs
     */
    public function overview()
    {
        $programs = Program::latest()->get();
        return view('admin.gass.gass', compact('programs'));
    }

    /**
     * Store or update physical accomplishment entries
     */public function save(Request $request)
{
    $validator = Validator::make($request->all(), $this->getValidationRules());

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $userId   = Auth::id();
    $officeId = $request->input('office_id', 1);

    $createdCount = 0;
    $updatedCount = 0;

    foreach ($request->entries as $data) {

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
            'year'        => $data['year'],
            'period_type' => $data['period_type'],
            'office_id'   => $officeId,
        ])->first();

        $record = Physical::updateOrCreate(
            [
                'programs_id' => $data['programs_id'],
                'year'        => $data['year'],
                'period_type' => $data['period_type'],
                'office_id'   => $officeId,
            ],
            [
                'user_id'               => $userId,
                'performance_indicator' => $data['performance_indicator'] ?? null,
                'target'                => $data['target'] ?? 0,
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
                'q1'  => $data['q1'] ?? 0,
                'q2'  => $data['q2'] ?? 0,
                'q3'  => $data['q3'] ?? 0,
                'q4'  => $data['q4'] ?? 0,
                'first_half'  => $data['first_half'] ?? 0,
                'second_half' => $data['second_half'] ?? 0,
                'annual_total'=> $data['annual_total'] ?? 0,
                'remarks'     => $data['remarks'] ?? null,
            ]
        );

        if ($existing) {
            $updatedCount++;
        } else {
            $createdCount++;
        }
    }

    return redirect()->route('admin.gass.physical')
        ->with('success',
            "$createdCount created, $updatedCount updated successfully."
        );
}

private function getValidationRules()
{
    return [
        'entries' => 'required|array',
    ];
}


    
}