<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Engp_Accomplishment;
use App\Models\Engp_Indicator;
use App\Models\Engp_Pap;
use App\Models\Engp_Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EngpController extends Controller
{
    public function index(Request $request, Engp_Pap $program = null)
    {
        $year = $request->query('year', now()->year);
        $office_id = $request->query('office_id', 1);
        $search = trim((string) $request->query('search', ''));

        $programsQuery = Engp_Pap::query();

        if ($program) {
            $programsQuery->whereKey($program->id);
        } else {
            $programsQuery->orderBy('created_at')->orderBy('id');
        }

        if ($search !== '') {
            $matchingOfficeIds = Office::query()
                ->where('name', 'like', "%{$search}%")
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $programsQuery->where(function ($query) use ($search, $matchingOfficeIds) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('program', 'like', "%{$search}%")
                    ->orWhere('project', 'like', "%{$search}%")
                    ->orWhere('activities', 'like', "%{$search}%")
                    ->orWhere('subactivities', 'like', "%{$search}%")
                    ->orWhereHas('indicators', function ($indicatorQuery) use ($search, $matchingOfficeIds) {
                        $indicatorQuery->where('name', 'like', "%{$search}%");

                        if (!empty($matchingOfficeIds)) {
                            $indicatorQuery->orWhere(function ($officeQuery) use ($matchingOfficeIds) {
                                foreach ($matchingOfficeIds as $officeId) {
                                    $officeQuery->orWhereJsonContains('office_id', $officeId)
                                        ->orWhereJsonContains('office_id', (string) $officeId);
                                }
                            });
                        }
                    });
            });
        }

        $programs = $programsQuery->get();
        $programIds = $programs->pluck('id')->all();

        $entries = collect();
        $existing = collect();

        $indicators = Engp_Indicator::whereIn('program_id', $programIds)->get()->groupBy('program_id');

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

        $targets = Engp_Target::where('year', $year)
            ->get()
            ->groupBy('indicator_id')
            ->map(function ($rows) {
                return $rows->mapWithKeys(function ($row) {
                    $officeKey = (string) ((int) ($row->office_id ?? 0));
                    return [$officeKey => $this->formatSectionRecordForJs($row)];
                })->toArray();
            })
            ->toArray();

        $accomplishments = Engp_Accomplishment::where('year', $year)
            ->get()
            ->groupBy('indicator_id')
            ->map(function ($rows) {
                return $rows->mapWithKeys(function ($row) {
                    $officeKey = (string) ((int) ($row->office_id ?? 0));
                    return [$officeKey => $this->formatSectionRecordForJs($row)];
                })->toArray();
            })
            ->toArray();

        $papTitles = Engp_Pap::query()
            ->select('title')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        $papProjects = Engp_Pap::query()
            ->select('project')
            ->whereNotNull('project')
            ->where('project', '!=', '')
            ->distinct()
            ->orderBy('project')
            ->pluck('project');

        $papActivities = Engp_Pap::query()
            ->select('activities')
            ->whereNotNull('activities')
            ->where('activities', '!=', '')
            ->distinct()
            ->orderBy('activities')
            ->pluck('activities');

        $papSubactivities = Engp_Pap::query()
            ->select('subactivities')
            ->whereNotNull('subactivities')
            ->where('subactivities', '!=', '')
            ->distinct()
            ->orderBy('subactivities')
            ->pluck('subactivities');

        $papPrefillData = Engp_Pap::query()
            ->with('indicators')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($pap) {
                return [
                    'id' => (int) $pap->id,
                    'title' => (string) ($pap->title ?? ''),
                    'program' => (string) ($pap->program ?? ''),
                    'project' => (string) ($pap->project ?? ''),
                    'activities' => (string) ($pap->activities ?? ''),
                    'subactivities' => (string) ($pap->subactivities ?? ''),
                    'indicators' => $pap->indicators->map(function ($indicator) {
                        return [
                            'id' => (int) $indicator->id,
                            'name' => (string) ($indicator->name ?? ''),
                            'indicator_type' => (string) ($indicator->indicator_type ?? ''),
                            'office_ids' => collect($indicator->office_id ?? [])
                                ->map(fn ($id) => (int) $id)
                                ->filter()
                                ->values()
                                ->all(),
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();

        $offices = Office::whereNull('parent_id')->with('children')->get();

        return view('admin.engp.engp_physical', compact(
            'entries',
            'programs',
            'existing',
            'indicators',
            'indicatorsForJs',
            'targets',
            'accomplishments',
            'offices',
            'papTitles',
            'papProjects',
            'papActivities',
            'papSubactivities',
            'papPrefillData',
            'year',
            'office_id',
            'search',
            'program'
        ));
    }

    public function storePap(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'program' => 'nullable|string|max:150',
            'project' => 'nullable|string|max:150',
            'activities' => 'nullable|string',
            'subactivities' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pap = Engp_Pap::create($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PAP saved successfully.',
                'pap' => $pap,
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function destroyPap(Engp_Pap $program)
    {
        DB::beginTransaction();
        try {
            Engp_Target::where('program_id', $program->id)->delete();
            Engp_Accomplishment::where('program_id', $program->id)->delete();
            Engp_Indicator::where('program_id', $program->id)->delete();
            $program->delete();

            DB::commit();
            return redirect()->back()->with('success', 'PAP deleted successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete PAP. Please try again.');
        }
    }

    public function storeIndicator(Request $request)
    {
        $baseValidated = $request->validate([
            'indicator_name' => 'required|string|max:255',
            'indicator_type' => 'nullable|string|in:non-comulative,comulative,semi-comulative',
            'program_id' => 'required|exists:engp_pap,id',
            'office_id' => 'required|array|min:1',
            'office_id.*' => 'required|exists:offices,id',
        ]);

        $indicatorName = trim($baseValidated['indicator_name']);
        $officeIds = collect($baseValidated['office_id'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $indicator = Engp_Indicator::firstOrNew([
            'program_id' => $baseValidated['program_id'],
            'name' => $indicatorName,
        ]);

        $wasNew = !$indicator->exists;

        $indicator->fill([
            'user_id' => Auth::id(),
            'name' => $indicatorName,
            'indicator_type' => $baseValidated['indicator_type'] ?? null,
            'office_id' => $officeIds,
        ]);
        $indicator->save();

        return response()->json([
            'message' => ($wasNew ? '1 created, 0 updated successfully.' : '0 created, 1 updated successfully.'),
            'success' => true,
            'created_count' => $wasNew ? 1 : 0,
            'updated_count' => $wasNew ? 0 : 1,
            'indicator' => $indicator,
        ]);
    }

    public function update(Request $request, Engp_Indicator $indicator)
    {
        $validated = $request->validate([
            'indicator_name' => 'nullable|string|max:255',
            'indicator_type' => 'nullable|string|in:non-comulative,comulative,semi-comulative',
            'office_id' => 'nullable|array|min:1',
            'office_id.*' => 'required|exists:offices,id',
        ]);

        $updateData = [];

        if (isset($validated['indicator_name']) && trim($validated['indicator_name']) !== '') {
            $updateData['name'] = trim($validated['indicator_name']);
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

        return response()->json([
            'message' => 'Indicator updated successfully.',
            'success' => true,
            'indicator' => $indicator,
        ]);
    }

    public function destroyIndicator(Request $request, Engp_Indicator $indicator)
    {
        $indicator->delete();

        return response()->json([
            'message' => 'Indicator deleted successfully.',
            'success' => true,
        ]);
    }

    public function storeTargets(Request $request)
    {
        $validated = $request->validate([
            'entries' => 'required|array',
            'entries.*.program_id' => 'required|exists:engp_pap,id',
            'entries.*.indicator_id' => 'required|exists:engp_indicators,id',
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
            $record = Engp_Target::firstOrNew([
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
            'entries.*.program_id' => 'required|exists:engp_pap,id',
            'entries.*.indicator_id' => 'required|exists:engp_indicators,id',
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
            $record = Engp_Accomplishment::firstOrNew([
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



