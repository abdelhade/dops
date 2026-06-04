<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\SpreadsheetExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceController extends Controller
{
    private function serviceHeaders(): array
    {
        return [
            __('dobs.service_name'),
            __('dobs.description'),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::latest()->get();
        return view('services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();
        return view('services.create');
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

        $validated['price'] = 0;

        Service::create($validated);

        return redirect()->route('services.index')->with('success', __('dobs.flash_service_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $this->authorizeEdit();
        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $service->update($validated);

        return redirect()->route('services.index')->with('success', __('dobs.flash_service_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $this->authorizeDelete();
        $service->delete();

        return redirect()->route('services.index')->with('success', __('dobs.flash_service_deleted'));
    }

    public function export(SpreadsheetExporter $exporter): StreamedResponse
    {
        $rows = Service::latest()->get()->map(fn (Service $s) => [
            $s->name,
            $s->description ?? '',
        ])->all();

        return $exporter->downloadXlsx('services', $this->serviceHeaders(), $rows);
    }

    public function template(SpreadsheetExporter $exporter): StreamedResponse
    {
        return $exporter->downloadTemplate('services', $this->serviceHeaders(), [
            __('dobs.import_sample_service_name'),
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

            Service::create([
                'name' => $name,
                'description' => ($row[1] ?? '') !== '' ? $row[1] : null,
                'price' => 0,
            ]);
            $imported++;
        }

        return redirect()
            ->route('services.index')
            ->with('success', __('dobs.flash_import_success', ['count' => $imported]));
    }
}
