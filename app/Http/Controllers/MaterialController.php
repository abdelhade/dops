<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Material;
use App\Support\SpreadsheetExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    private function materialHeaders(): array
    {
        return [
            __('dobs.material_name'),
            __('dobs.description'),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materials = Material::latest()->get();
        return view('materials.index', compact('materials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();
        return view('materials.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['unit'] = '-';
        $validated['price'] = 0;
        $validated['stock'] = 0;

        Material::create($validated);

        return redirect()->route('materials.index')->with('success', __('dobs.flash_material_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        return view('materials.show', compact('material'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material)
    {
        $this->authorizeEdit();
        return view('materials.edit', compact('material'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $material->update($validated);

        return redirect()->route('materials.index')->with('success', __('dobs.flash_material_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        $this->authorizeDelete();
        $material->delete();

        return redirect()->route('materials.index')->with('success', __('dobs.flash_material_deleted'));
    }

    public function export(SpreadsheetExporter $exporter): StreamedResponse
    {
        $rows = Material::latest()->get()->map(fn (Material $m) => [
            $m->name,
            $m->description ?? '',
        ])->all();

        return $exporter->downloadXlsx('materials', $this->materialHeaders(), $rows);
    }

    public function template(SpreadsheetExporter $exporter): StreamedResponse
    {
        return $exporter->downloadTemplate('materials', $this->materialHeaders(), [
            __('dobs.import_sample_material_name'),
            __('dobs.import_sample_description'),
        ]);
    }

    public function import(Request $request, SpreadsheetExporter $exporter): RedirectResponse
    {
        $this->authorizeCreate();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $imported = 0;

        foreach ($exporter->readDataRows($request->file('file')) as $row) {
            $name = $row[0] ?? '';
            if ($name === '') {
                continue;
            }

            Material::create([
                'name' => $name,
                'description' => ($row[1] ?? '') !== '' ? $row[1] : null,
                'unit' => '-',
                'price' => 0,
                'stock' => 0,
            ]);
            $imported++;
        }

        return redirect()
            ->route('materials.index')
            ->with('success', __('dobs.flash_import_success', ['count' => $imported]));
    }
}
