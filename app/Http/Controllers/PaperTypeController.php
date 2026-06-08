<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaperType;
use Illuminate\Http\Request;

class PaperTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paperTypes = PaperType::latest()->get();
        return view('paper_types.index', compact('paperTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();
        return view('paper_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight_gsm' => 'nullable|integer|min:0',
            'finish' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        PaperType::create($validated);

        return redirect()->route('paper-types.index')->with('success', __('dobs.flash_paper_type_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PaperType $paperType)
    {
        return view('paper_types.show', compact('paperType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaperType $paperType)
    {
        $this->authorizeEdit();
        return view('paper_types.edit', compact('paperType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaperType $paperType)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight_gsm' => 'nullable|integer|min:0',
            'finish' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $paperType->update($validated);

        return redirect()->route('paper-types.index')->with('success', __('dobs.flash_paper_type_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaperType $paperType)
    {
        return $this->destroyRecord($paperType, 'paper-types.index', 'dobs.flash_paper_type_deleted');
    }
}
