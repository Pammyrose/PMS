<?php

namespace App\Http\Controllers;
use App\Models\Gass_Pap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
public function index()
{
    $programs = Gass_Pap::latest()->get();

return view('admin.programs', ['programs' => $programs]);
}

public function create()
    {
        return view('programs'); // same view — or make a separate create.blade.php
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'program'       => 'nullable|string|max:150',
            'project'       => 'nullable|string|max:150',
            'activities'    => 'nullable|string',
            'subactivities' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            Gass_Pap::create($validated); // cleaner

            DB::commit();

            return redirect()
                ->route('programs.index')           // ← better than back()
                ->with('success', 'New program / target has been successfully saved.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save: ' . $e->getMessage()]);
        }
    }

    public function edit(Gass_Pap $program)
    {
        $programs = Gass_Pap::latest()->get();           // ← add this line
    
    return view('admin.programs', [
        'programs' => $programs,
        'program'  => $program,
    ]);
    }

public function update(Request $request, Gass_Pap $program)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'program'       => 'nullable|string|max:150',
            'project'       => 'nullable|string|max:150',
            'activities'    => 'nullable|string',
            'subactivities' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $program->update($validated);

            DB::commit();

            return redirect()
                ->route('programs.index')
                ->with('success', 'Program has been successfully updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update: ' . $e->getMessage()]);
        }
    }
}
