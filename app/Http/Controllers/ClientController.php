<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Support\SpreadsheetExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    private function clientHeaders(): array
    {
        return [
            __('dobs.client_name'),
            __('dobs.phone'),
            __('dobs.email'),
            __('dobs.address'),
            __('dobs.col_notes_header'),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::latest()->get();
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')->with('success', __('dobs.flash_client_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $this->authorizeEdit();
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', __('dobs.flash_client_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $this->authorizeDelete();
        $client->delete();

        return redirect()->route('clients.index')->with('success', __('dobs.flash_client_deleted'));
    }

    public function export(SpreadsheetExporter $exporter): StreamedResponse
    {
        $headers = $this->clientHeaders();
        $rows = Client::latest()->get()->map(fn (Client $c) => [
            $c->name,
            $c->phone ?? '',
            $c->email ?? '',
            $c->address ?? '',
            $c->notes ?? '',
        ])->all();

        return $exporter->downloadXlsx('clients', $headers, $rows);
    }

    public function template(SpreadsheetExporter $exporter): StreamedResponse
    {
        return $exporter->downloadTemplate('clients', $this->clientHeaders(), [
            __('dobs.import_sample_client_name'),
            '0500000000',
            'client@example.com',
            __('dobs.import_sample_address'),
            '',
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

            Client::create([
                'name' => $name,
                'phone' => $row[1] ?? null,
                'email' => ($row[2] ?? '') !== '' ? $row[2] : null,
                'address' => ($row[3] ?? '') !== '' ? $row[3] : null,
                'notes' => ($row[4] ?? '') !== '' ? $row[4] : null,
            ]);
            $imported++;
        }

        return redirect()
            ->route('clients.index')
            ->with('success', __('dobs.flash_import_success', ['count' => $imported]));
    }
}
