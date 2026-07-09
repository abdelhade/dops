<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Operation;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user && $user->isDataEntry()) {
            return redirect()->route('operation-movements.index');
        }

        $stats = [
            'suppliers_count' => Supplier::count(),
            'items_count' => Item::count(),
            'operations_count' => Operation::count(),
            'total_sales' => (float) Operation::sum('total_amount'),
        ];

        $recent_operations = Operation::with(['item', 'operationStatus'])
            ->latest()
            ->take(5)
            ->get();

        $low_stock_items = Item::where('stock', '<', 50)
            ->orderBy('stock')
            ->take(5)
            ->get();

        $chartData = $this->buildChartData();

        return view('dashboard', compact('stats', 'recent_operations', 'low_stock_items', 'chartData'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChartData(): array
    {
        $unspecified = __('dobs.na');

        $operationsByStatus = Operation::query()
            ->leftJoin('operation_statuses', 'operations.operation_status_id', '=', 'operation_statuses.id')
            ->select(
                'operation_statuses.name',
                'operation_statuses.color',
                DB::raw('COUNT(operations.id) as count'),
            )
            ->groupBy('operation_statuses.id', 'operation_statuses.name', 'operation_statuses.color')
            ->orderByRaw('operation_statuses.sort_order IS NULL, operation_statuses.sort_order')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->name ?? $unspecified,
                'count' => (int) $row->count,
                'color' => $row->color ?? '#6c757d',
            ])
            ->values()
            ->all();

        $monthKeys = collect(range(5, 0))
            ->map(fn (int $offset) => now()->subMonths($offset)->format('Y-m'))
            ->values();

        $monthlyRaw = Operation::query()
            ->whereDate('operation_date', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['operation_date', 'total_amount'])
            ->groupBy(fn (Operation $operation) => $operation->operation_date?->format('Y-m') ?? '')
            ->map(fn ($group, string $month) => (object) [
                'month' => $month,
                'count' => $group->count(),
                'revenue' => (float) $group->sum('total_amount'),
            ]);

        $monthlyOperations = $monthKeys->map(function (string $month) use ($monthlyRaw) {
            $row = $monthlyRaw->get($month);

            return [
                'label' => Carbon::createFromFormat('Y-m', $month)
                    ->locale(app()->getLocale())
                    ->translatedFormat('M Y'),
                'count' => (int) ($row->count ?? 0),
                'revenue' => (float) ($row->revenue ?? 0),
            ];
        })->values()->all();

        $itemsByCategory = Item::query()
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'categories.name',
                DB::raw('COUNT(items.id) as count'),
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->name ?? $unspecified,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();

        $paperTypesUsage = Operation::query()
            ->leftJoin('paper_types', 'operations.paper_type_id', '=', 'paper_types.id')
            ->select(
                'paper_types.name',
                DB::raw('COUNT(operations.id) as count'),
            )
            ->groupBy('paper_types.id', 'paper_types.name')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->name ?? $unspecified,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();

        return [
            'operations_by_status' => $operationsByStatus,
            'monthly_operations' => $monthlyOperations,
            'items_by_category' => $itemsByCategory,
            'paper_types_usage' => $paperTypesUsage,
            'labels' => [
                'operations_count' => __('dobs.chart_operations_count'),
                'items_count' => __('dobs.chart_items_count'),
                'revenue' => __('dobs.chart_revenue'),
                'currency' => __('dobs.currency'),
            ],
        ];
    }
}
