<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaperType;
use App\Support\SpreadsheetExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaperTypeController extends Controller
{
    private function paperTypeHeaders(): array
    {
        return [
            __('dobs.paper_type_name'),
            __('dobs.weight_gsm'),
            __('dobs.finish'),
            __('dobs.description'),
        ];
    }

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

    public function export(SpreadsheetExporter $exporter): StreamedResponse
    {
        $rows = PaperType::latest()->get()->map(fn (PaperType $p) => [
            $p->name,
            $p->weight_gsm ?? '',
            $p->finish ?? '',
            $p->description ?? '',
        ])->all();

        return $exporter->downloadXlsx('paper-types', $this->paperTypeHeaders(), $rows);
    }

    public function template(SpreadsheetExporter $exporter): StreamedResponse
    {
        return $exporter->downloadTemplate('paper-types', $this->paperTypeHeaders(), [
            __('dobs.import_sample_paper_type_name'),
            __('dobs.import_sample_weight_gsm'),
            __('dobs.import_sample_finish'),
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

            $weight = $row[1] ?? '';

            PaperType::create([
                'name' => $name,
                'weight_gsm' => is_numeric($weight) ? (int) $weight : null,
                'finish' => ($row[2] ?? '') !== '' ? $row[2] : null,
                'description' => ($row[3] ?? '') !== '' ? $row[3] : null,
            ]);
            $imported++;
        }

        return redirect()
            ->route('paper-types.index')
            ->with('success', __('dobs.flash_import_success', ['count' => $imported]));
    }
}
