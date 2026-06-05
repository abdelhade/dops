<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function paperMaterialsSummary(Request $request)
    {
        $query = $this->filteredOperationsQuery($request);

        $operations = (clone $query)
            ->with(['item', 'paperType', 'operationStatus'])
            ->latest()
            ->get();

        $rows = (clone $query)
            ->leftJoin('paper_types', 'operations.paper_type_id', '=', 'paper_types.id')
            ->select(
                'operations.paper_type_id',
                'paper_types.name as paper_type_name',
                DB::raw('COALESCE(SUM(operations.pull_count), 0) as total_pull_count'),
                DB::raw('COALESCE(SUM(operations.quantity_per_sheet), 0) as total_quantity_per_sheet'),
                DB::raw('COUNT(*) as operations_count'),
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
            'operations_count' => (int) $rows->sum('operations_count'),
        ];

        return view('reports.paper-materials-summary', compact('operations', 'rows', 'totals'));
    }

    private function filteredOperationsQuery(Request $request)
    {
        $query = Operation::query();

        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->date_to);
        }

        return $query;
    }
}
