<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\PaperType;
use App\Models\Service;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** @var list<string> */
    private const FILTER_KEYS = [
        'search', 'operation_number', 'date_from', 'date_to', 'client_id', 'item_id', 'quantity',
        'statement', 'printing_supplier_id', 'ctp_supplier_id', 'color_count', 'paper_type_id',
        'job_size', 'pull_count', 'quantity_per_sheet', 'service_1_id', 'service_2_id', 'service_3_id',
        'operation_status_id', 'notes',
    ];

    public function paperMaterialsSummary(Request $request)
    {
        $filtersApplied = $this->filtersApplied($request);

        if (! $filtersApplied) {
            return view('reports.paper-materials-summary', array_merge(
                [
                    'filtersApplied' => false,
                    'operations' => collect(),
                    'rows' => collect(),
                    'totals' => (object) [
                        'total_pull_count' => 0,
                        'total_quantity_per_sheet' => 0,
                    ],
                ],
                $this->filterOptions(),
            ));
        }

        $query = $this->filteredOperationsQuery($request);

        $query->whereHas('operationType', function($q) {
            $q->where('form_mode', \App\Enums\OperationTypeMode::Offset);
        });

        $operations = (clone $query)
            ->with([
                'client', 'item', 'paperType', 'operationStatus',
                'printingSupplier', 'ctpSupplier',
                'service1', 'service2', 'service3',
            ])
            ->orderBy('operation_date')
            ->orderBy('id')
            ->get();

        $rows = (clone $query)
            ->leftJoin('paper_types', 'operations.paper_type_id', '=', 'paper_types.id')
            ->select(
                'operations.paper_type_id',
                'paper_types.name as paper_type_name',
                DB::raw('COALESCE(SUM(COALESCE(operations.pull_count, 0) * COALESCE(operations.quantity_per_sheet, 0)), 0) as total_pull_count'),
                DB::raw('COALESCE(SUM(operations.quantity_per_sheet), 0) as total_quantity_per_sheet'),
            )
            ->groupBy('operations.paper_type_id', 'paper_types.name')
            ->orderByRaw('paper_types.name IS NULL, paper_types.name')
            ->get()
            ->map(function ($row) {
                $row->paper_type_name = $row->paper_type_name ?? __('dobs.report_unspecified_paper_type');

                return $row;
            });

        $totals = (object) [
            'total_pull_count' => (int) $rows->sum('total_pull_count'),
            'total_quantity_per_sheet' => (int) $rows->sum('total_quantity_per_sheet'),
        ];

        return view('reports.paper-materials-summary', array_merge(
            compact('operations', 'rows', 'totals', 'filtersApplied'),
            $this->filterOptions(),
        ));
    }

    public function generalOperationsSummary(Request $request)
    {
        $filtersApplied = $this->filtersApplied($request);

        if (! $filtersApplied) {
            return view('reports.general-operations-summary', array_merge(
                [
                    'filtersApplied' => false,
                    'operations' => collect(),
                    'rows' => collect(),
                    'totals' => (object) [
                        'total_quantity' => 0,
                    ],
                ],
                $this->filterOptions(),
            ));
        }

        $query = $this->filteredOperationsQuery($request);

        $query->whereHas('operationType', function($q) {
            $q->where('form_mode', \App\Enums\OperationTypeMode::General);
        });

        $operations = (clone $query)
            ->with([
                'client', 'item', 'operationStatus',
                'printingSupplier', 'operationKind'
            ])
            ->orderBy('operation_date')
            ->orderBy('id')
            ->get();

        $rows = (clone $query)
            ->leftJoin('operation_kinds', 'operations.operation_kind_id', '=', 'operation_kinds.id')
            ->select(
                'operations.operation_kind_id',
                'operation_kinds.name as operation_kind_name',
                DB::raw('COALESCE(SUM(operations.quantity), 0) as total_quantity'),
            )
            ->groupBy('operations.operation_kind_id', 'operation_kinds.name')
            ->orderByRaw('operation_kinds.name IS NULL, operation_kinds.name')
            ->get()
            ->map(function ($row) {
                $row->operation_kind_name = $row->operation_kind_name ?? __('dobs.report_unspecified_kind');
                return $row;
            });

        $totals = (object) [
            'total_quantity' => (int) $rows->sum('total_quantity'),
        ];

        return view('reports.general-operations-summary', array_merge(
            compact('operations', 'rows', 'totals', 'filtersApplied'),
            $this->filterOptions(),
        ));
    }

    private function filtersApplied(Request $request): bool
    {
        if ($request->boolean('applied')) {
            return true;
        }

        return collect(self::FILTER_KEYS)->contains(fn (string $key) => $request->filled($key));
    }

    /**
     * @return array<string, mixed>
     */
    private function filterOptions(): array
    {
        return [
            'clients' => Client::orderBy('name')->get(),
            'items' => Item::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'paperTypes' => PaperType::orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
            'operationStatuses' => OperationStatus::orderBy('sort_order')->get(),
        ];
    }

    private function filteredOperationsQuery(Request $request)
    {
        $query = Operation::query();

        if ($request->filled('operation_number')) {
            $query->where('operation_number', 'like', '%' . $request->operation_number . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->date_to);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('quantity')) {
            $query->where('quantity', $request->quantity);
        }
        if ($request->filled('operation_status_id')) {
            $query->where('operation_status_id', $request->operation_status_id);
        }
        if ($request->filled('printing_supplier_id')) {
            $query->where('printing_supplier_id', $request->printing_supplier_id);
        }
        if ($request->filled('ctp_supplier_id')) {
            $query->where('ctp_supplier_id', $request->ctp_supplier_id);
        }
        if ($request->filled('paper_type_id')) {
            $query->where('paper_type_id', $request->paper_type_id);
        }
        if ($request->filled('color_count')) {
            $query->where('color_count', $request->color_count);
        }
        if ($request->filled('job_size')) {
            $query->where('job_size', $request->job_size);
        }
        if ($request->filled('pull_count')) {
            $query->where('pull_count', $request->pull_count);
        }
        if ($request->filled('quantity_per_sheet')) {
            $query->where('quantity_per_sheet', $request->quantity_per_sheet);
        }
        if ($request->filled('service_1_id')) {
            $query->where('service_1_id', $request->service_1_id);
        }
        if ($request->filled('service_2_id')) {
            $query->where('service_2_id', $request->service_2_id);
        }
        if ($request->filled('service_3_id')) {
            $query->where('service_3_id', $request->service_3_id);
        }
        if ($request->filled('statement')) {
            $query->where('statement', 'like', '%' . $request->statement . '%');
        }
        if ($request->filled('notes')) {
            $query->where('notes', 'like', '%' . $request->notes . '%');
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('operation_number', 'like', $term)
                    ->orWhere('statement', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('client', fn ($clientQuery) => $clientQuery->where('name', 'like', $term))
                    ->orWhereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $term))
                    ->orWhereHas('paperType', fn ($paperQuery) => $paperQuery->where('name', 'like', $term))
                    ->orWhereHas('printingSupplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', $term))
                    ->orWhereHas('ctpSupplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', $term))
                    ->orWhereHas('operationStatus', fn ($statusQuery) => $statusQuery->where('name', 'like', $term))
                    ->orWhereHas('service1', fn ($serviceQuery) => $serviceQuery->where('name', 'like', $term))
                    ->orWhereHas('service2', fn ($serviceQuery) => $serviceQuery->where('name', 'like', $term))
                    ->orWhereHas('service3', fn ($serviceQuery) => $serviceQuery->where('name', 'like', $term));
            });
        }

        return $query;
    }

    public function operationsKanban(Request $request)
    {
        $statuses = OperationStatus::orderBy('sort_order')->get();

        return view('reports.operations-kanban', compact('statuses'));
    }

    public function operationsKanbanLoad(Request $request)
    {
        $validated = $request->validate([
            'operation_status_id' => 'required|exists:operation_statuses,id',
            'page' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:200',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $perPage = 20;
        $page = (int) ($validated['page'] ?? 1);

        $query = Operation::query()
            ->where('operation_status_id', $validated['operation_status_id'])
            ->with([
                'client', 'item', 'paperType', 'printingSupplier',
                'service1', 'service2', 'service3',
            ]);

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('operation_number', 'like', $term)
                    ->orWhere('statement', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('client', fn ($clientQuery) => $clientQuery->where('name', 'like', $term))
                    ->orWhereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $term));
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->date_to);
        }

        $paginated = $query
            ->orderByDesc('operation_date')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'operations' => collect($paginated->items())
                ->map(fn (Operation $operation) => $this->serializeKanbanOperation($operation))
                ->values()
                ->all(),
            'has_more' => $paginated->hasMorePages(),
            'next_page' => $paginated->currentPage() + 1,
            'total' => $paginated->total(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeKanbanOperation(Operation $operation): array
    {
        $services = collect([
            $operation->service1?->name,
            $operation->service2?->name,
            $operation->service3?->name,
        ])->filter()->values()->all();

        return [
            'id' => $operation->id,
            'operation_number' => $operation->operation_number,
            'operation_date' => $operation->operation_date?->format('Y-m-d'),
            'operation_time' => $operation->formattedOperationTime(),
            'client_name' => $operation->client?->name,
            'item_name' => $operation->item?->name,
            'quantity' => $operation->quantity,
            'color_count' => $operation->color_count,
            'pull_count' => $operation->pull_count,
            'statement' => $operation->statement ?? $operation->notes,
            'paper_type_name' => $operation->paperType?->name,
            'printing_supplier_name' => $operation->printingSupplier?->name,
            'services' => $services,
            'show_url' => route('operations.show', $operation->id),
        ];
    }
}
