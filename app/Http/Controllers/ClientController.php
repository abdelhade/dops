<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Support\SpreadsheetExporter;
use App\Support\DaftaraClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
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
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('phone', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('address', 'like', $term)
                  ->orWhere('notes', 'like', $term);
            });
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            $canBulkDelete = (bool) auth()->user()?->canDeleteRecords();
            return response()->json([
                'html' => view('clients._rows', compact('clients', 'canBulkDelete'))->render(),
                'has_more' => $clients->hasMorePages(),
                'next_page_url' => $clients->nextPageUrl(),
            ]);
        }

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
        return $this->destroyRecord($client, 'clients.index', 'dobs.flash_client_deleted');
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:clients,id'],
        ]);

        $result = $this->bulkDestroyRecords(Client::class, $validated['ids']);

        $message = match (true) {
            $result['deleted'] > 0 && $result['skipped'] > 0 => __('dobs.flash_clients_bulk_partial', [
                'deleted' => $result['deleted'],
                'skipped' => $result['skipped'],
            ]),
            $result['deleted'] > 0 => __('dobs.flash_clients_bulk_deleted', ['count' => $result['deleted']]),
            default => __('dobs.cannot_delete_has_related'),
        };

        return response()->json([
            'success' => $result['deleted'] > 0,
            'message' => $message,
            'deleted' => $result['deleted'],
            'skipped' => $result['skipped'],
            'deleted_ids' => $result['deleted_ids'],
        ]);
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
            'file' => ['required', File::types(['xlsx', 'xls', 'csv'])->max(5120)],
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

    public function daftaraSyncForm(DaftaraClient $daftaraClient)
    {
        $this->authorizeCreate();

        if (! $daftaraClient->isConfigured()) {
            return redirect()
                ->route('clients.index')
                ->with('error', __('dobs.daftara_not_configured'));
        }

        $daftaraClients = $daftaraClient->getClients();

        $localClientNames = Client::pluck('name')
            ->map(fn($name) => mb_strtolower(trim($name)))
            ->toArray();

        $missingClients = [];
        foreach ($daftaraClients as $client) {
            $normalizedName = mb_strtolower(trim($client['name']));
            if (! in_array($normalizedName, $localClientNames, true)) {
                $missingClients[] = $client;
            }
        }

        return view('clients.sync', compact('missingClients'));
    }

    public function daftaraSync(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'clients' => 'required|array',
            'clients.*.name' => 'required|string|max:255',
            'clients.*.phone' => 'nullable|string|max:50',
            'clients.*.email' => 'nullable|email|max:255',
            'clients.*.address' => 'nullable|string',
            'clients.*.notes' => 'nullable|string',
        ]);

        $imported = 0;
        foreach ($validated['clients'] as $clientData) {
            $normalizedName = trim($clientData['name']);
            if (! Client::where('name', $normalizedName)->exists()) {
                Client::create([
                    'name' => $normalizedName,
                    'phone' => $clientData['phone'] ?? null,
                    'email' => $clientData['email'] ?? null,
                    'address' => $clientData['address'] ?? null,
                    'notes' => $clientData['notes'] ?? null,
                ]);
                $imported++;
            }
        }

        return redirect()
            ->route('clients.index')
            ->with('success', __('dobs.flash_daftara_sync_success', ['count' => $imported]));
    }
}
