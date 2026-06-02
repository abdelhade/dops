<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stages = Stage::orderBy('sort_order')->get();
        return view('stages.index', compact('stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();
        return view('stages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        Stage::create($validated);

        return redirect()->route('stages.index')->with('success', __('dobs.flash_stage_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Stage $stage)
    {
        return view('stages.show', compact('stage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stage $stage)
    {
        $this->authorizeEdit();
        return view('stages.edit', compact('stage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stage $stage)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $stage->update($validated);

        return redirect()->route('stages.index')->with('success', __('dobs.flash_stage_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stage $stage)
    {
        $this->authorizeDelete();
        $stage->delete();

        return redirect()->route('stages.index')->with('success', __('dobs.flash_stage_deleted'));
    }
}
