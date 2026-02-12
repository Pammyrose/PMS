<?php

namespace App\Http\Controllers;
use App\Models\Pap;
use App\Models\Office;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeController extends Controller
{
    public function create()
    {
        $regional = Office::whereNull('parent_id')->first();

        // Option 1: Include Regional + all PENROs
        $penros = Office::whereNull('parent_id')          // Regional
            ->union(Office::where('parent_id', $regional?->id ?? 0))
            ->get();


        $programs = Program::select('title','program','activities','subactivities','project')->latest()->get();

        return view('admin.target_form', compact('penros', 'programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Main fields
            'title' => 'required|string|max:255',
            'program' => 'nullable|string|max:150',
            'project' => 'nullable|string|max:150',
            'activities' => 'nullable|string',
            'subactivities' => 'nullable|string',
            'target' => 'required|integer|min:0',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'indicators' => 'nullable|string',

            // Office hierarchy - PENRO is required, CENRO optional
            'penro_id' => 'required|exists:offices,id',
            'cenro_id' => 'nullable|exists:offices,id',

            // Supporting divisions (checkboxes)
            'div' => 'nullable|array',
            'div.*' => 'string|in:PMD,CDD,LD,LPDD,FD,SMD,AD,ED',
        ]);

        try {
            DB::beginTransaction();

            // Choose the most specific office
            $office_id = $request->filled('cenro_id')
                ? $request->cenro_id
                : $request->penro_id;

            $pap = Pap::create([
                'title' => $validated['title'],
                'program' => $validated['program'] ?? null,
                'project' => $validated['project'] ?? null,
                'activities' => $validated['activities'] ?? null,
                'subactivities' => $validated['subactivities'] ?? null,
                'target' => $validated['target'],
                'budget' => $validated['budget'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'office_id' => $office_id,
                'indicators' => $validated['indicators'] ?? null,
                'div' => $request->input('div', []),
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'New program / target has been successfully saved.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save. ' . $e->getMessage()]);
        }
    }


    public function getCenros(Office $penro)
    {

        $cenros = $penro->children()->get(['id', 'name']);

        return response()->json($cenros);
    }
}
