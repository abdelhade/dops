<?php

namespace App\Http\Controllers;

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
        'search', 'operation_number', 'date_from', 'date_to', 'item_id', 'operation_status_id',
        'printing_supplier_id', 'ctp_supplier_id', 'paper_type_id', 'color_count', 'service_id', 'statement',
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
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
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
        if ($request->filled('service_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('service_1_id', $request->service_id)
                    ->orWhere('service_2_id', $request->service_id)
                    ->orWhere('service_3_id', $request->service_id);
            });
        }
        if ($request->filled('statement')) {
            $query->where(function ($q) use ($request) {
                $q->where('statement', 'like', '%' . $request->statement . '%')
                    ->orWhere('notes', 'like', '%' . $request->statement . '%');
            });
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('operation_number', 'like', $term)
                    ->orWhere('statement', 'like', $term)
                    ->orWhere('notes', 'like', $term)
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
}
