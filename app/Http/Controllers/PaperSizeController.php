<?php

namespace App\Http\Controllers;

use App\Models\PaperSize;
use Illuminate\Http\Request;

class PaperSizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paperSizes = PaperSize::withCount('items')->latest()->get();
        return view('paper_sizes.index', compact('paperSizes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();

        return view('paper_sizes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:paper_sizes,name',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
        ]);

        PaperSize::create($validated);

        return redirect()->route('paper-sizes.index')->with('success', __('dobs.flash_paper_size_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PaperSize $paperSize)
    {
        $paperSize->load('items.category', 'items.supplier');
        return view('paper_sizes.show', compact('paperSize'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaperSize $paperSize)
    {
        $this->authorizeEdit();

        return view('paper_sizes.edit', compact('paperSize'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaperSize $paperSize)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:paper_sizes,name,' . $paperSize->id,
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
        ]);

        $paperSize->update($validated);

        return redirect()->route('paper-sizes.index')->with('success', __('dobs.flash_paper_size_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaperSize $paperSize)
    {
        $this->authorizeDelete();

        $paperSize->delete();
        return redirect()->route('paper-sizes.index')->with('success', __('dobs.flash_paper_size_deleted'));
    }
}
