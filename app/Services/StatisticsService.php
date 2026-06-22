<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OperationTypeMode;
use App\Models\Item;
use App\Models\Operation;
use App\Models\OperationLog;
use App\Models\OperationStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    private const PRINTING_STATUS_ID = 8;

    /**
     * @param  list<int>|null  $operationStatusIds
     * @return array<string, mixed>
     */
    public function build(
        string $dateFrom,
        string $dateTo,
        ?array $operationStatusIds = null,
        ?int $leadTimeFromStatusId = null,
        ?int $leadTimeToStatusId = null,
    ): array {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();
        $periodDays = max(1, (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);

        $statuses = OperationStatus::query()->orderBy('sort_order')->get()->keyBy('id');
        $leadTimeBounds = $this->resolveLeadTimeBounds($statuses, $leadTimeFromStatusId, $leadTimeToStatusId);

        $printingStatusId = $statuses->has(self::PRINTING_STATUS_ID)
            ? self::PRINTING_STATUS_ID
            : (int) ($statuses->first(
                fn (OperationStatus $status) => str_contains(mb_strtolower($status->name), 'مطبعة')
                    || str_contains(mb_strtolower($status->name), 'print')
            )?->id ?? 0);

        $operationsQuery = Operation::query()
            ->whereDate('operation_date', '>=', $from->toDateString())
            ->whereDate('operation_date', '<=', $to->toDateString());

        if ($operationStatusIds !== null && $operationStatusIds !== []) {
            $operationsQuery->whereIn('operation_status_id', $operationStatusIds);
        }

        $operationIds = (clone $operationsQuery)->pluck('id');
        $totalOperations = $operationIds->count();

        $timelines = $this->buildStatusTimelines($operationIds);
        $dwellStats = $this->calculateDwellStats($timelines, $statuses);

        $offsetQuery = (clone $operationsQuery)->whereHas(
            'operationType',
            fn ($query) => $query->where('form_mode', OperationTypeMode::Offset)
        );

        $generalQuery = (clone $operationsQuery)->whereHas(
            'operationType',
            fn ($query) => $query->where('form_mode', OperationTypeMode::General)
        );

        $offsetOutput = (int) (clone $offsetQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(pull_count, 0) * COALESCE(quantity_per_sheet, 0)), 0) as total')
            ->value('total');

        $generalOutput = (int) (clone $generalQuery)->sum('quantity');
        $totalPullCount = (int) (clone $offsetQuery)->sum('pull_count');

        $leadTimes = $this->calculateLeadTimes(
            $timelines,
            $leadTimeBounds['start_id'],
            $leadTimeBounds['end_ids'],
            $operationIds,
        );

        $printingTurnaround = $this->supplierPrintingTurnaround(
            $operationsQuery,
            $timelines,
            $printingStatusId,
        );

        $ctpEfficiency = $this->ctpSupplierEfficiency($operationsQuery, $timelines, $leadTimeBounds['start_id']);

        $wasteStats = $this->materialWasteStats($offsetQuery);
        $stockAnomalyRate = $this->stockAnomalyRate();
        $paperTurnover = $this->paperMaterialTurnover($operationsQuery, $periodDays);

        $clientRetention = $this->clientRetentionRate($operationsQuery);
        $profitability = $this->orderProfitability($operationsQuery);
        $topItems = $this->topSellingItems($operationsQuery);

        $modificationRate = $this->orderModificationRate($operationIds, $totalOperations);
        $jobFailureRate = $this->jobFailureRate();

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'days' => $periodDays,
            ],
            'operational' => [
                'avg_lead_time_days' => $leadTimes['average_days'],
                'completed_lead_samples' => $leadTimes['sample_count'],
                'lead_time_from_status_id' => $leadTimeBounds['start_id'],
                'lead_time_to_status_id' => $leadTimeBounds['selected_end_id'],
                'lead_time_from_status_name' => $leadTimeBounds['start_name'],
                'lead_time_to_status_name' => $leadTimeBounds['end_name'],
                'sop_compliance_rate' => $dwellStats['compliance_rate'],
                'sop_compliant_periods' => $dwellStats['compliant_periods'],
                'sop_total_periods' => $dwellStats['total_periods'],
                'throughput_pulls_per_day' => round($totalPullCount / $periodDays, 2),
                'throughput_offset_output_per_day' => round($offsetOutput / $periodDays, 2),
                'throughput_general_qty_per_day' => round($generalOutput / $periodDays, 2),
                'total_operations' => $totalOperations,
                'offset_output' => $offsetOutput,
                'general_output' => $generalOutput,
            ],
            'supplier' => [
                'printing_turnaround' => $printingTurnaround,
                'ctp_efficiency' => $ctpEfficiency,
            ],
            'inventory' => [
                'waste_rate' => $wasteStats['waste_rate'],
                'yield_rate' => $wasteStats['yield_rate'],
                'waste_samples' => $wasteStats['sample_count'],
                'stock_anomaly_rate' => $stockAnomalyRate['rate'],
                'negative_stock_items' => $stockAnomalyRate['negative_count'],
                'total_items' => $stockAnomalyRate['total_count'],
                'paper_turnover' => $paperTurnover,
            ],
            'commercial' => [
                'client_retention_rate' => $clientRetention['rate'],
                'active_clients' => $clientRetention['active_clients'],
                'returning_clients' => $clientRetention['returning_clients'],
                'total_revenue' => $profitability['revenue'],
                'total_cost' => $profitability['cost'],
                'net_profit' => $profitability['profit'],
                'profit_margin' => $profitability['margin'],
                'top_items' => $topItems,
            ],
            'system' => [
                'modification_rate' => $modificationRate['rate'],
                'modified_operations' => $modificationRate['modified_count'],
                'job_failure_rate' => $jobFailureRate['rate'],
                'failed_jobs' => $jobFailureRate['failed'],
                'total_jobs' => $jobFailureRate['total'],
            ],
            'chart' => [
                'supplier_turnaround' => collect($printingTurnaround)
                    ->sortBy('avg_days')
                    ->take(8)
                    ->values()
                    ->all(),
                'top_items' => collect($topItems)->take(8)->values()->all(),
                'paper_turnover' => collect($paperTurnover)->take(8)->values()->all(),
                'ctp_repeat_rate' => collect($ctpEfficiency)
                    ->sortByDesc('repeat_rate')
                    ->take(8)
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * @param  Collection<int, int>  $operationIds
     * @return array<int, list<array{status_id: int, at: Carbon}>>
     */
    private function buildStatusTimelines(Collection $operationIds): array
    {
        if ($operationIds->isEmpty()) {
            return [];
        }

        $timelines = [];

        OperationLog::query()
            ->whereIn('operation_id', $operationIds)
            ->where('action', OperationLog::ACTION_STATUS_CHANGED)
            ->orderBy('operation_id')
            ->orderBy('created_at')
            ->get(['operation_id', 'created_at', 'changes'])
            ->each(function (OperationLog $log) use (&$timelines): void {
                $toStatus = $log->changes['operation_status_id']['to'] ?? null;

                if ($toStatus === null || $toStatus === '') {
                    return;
                }

                $timelines[$log->operation_id][] = [
                    'status_id' => (int) $toStatus,
                    'at' => $log->created_at,
                ];
            });

        return $timelines;
    }

    /**
     * @param  array<int, list<array{status_id: int, at: Carbon}>>  $timelines
     * @param  Collection<int, OperationStatus>  $statuses
     * @return array{compliance_rate: float, compliant_periods: int, total_periods: int}
     */
    private function calculateDwellStats(array $timelines, Collection $statuses): array
    {
        $compliant = 0;
        $total = 0;

        foreach ($timelines as $events) {
            $events = collect($events)->sortBy('at')->values();

            for ($index = 0; $index < $events->count() - 1; $index++) {
                $statusId = (int) $events[$index]['status_id'];
                $status = $statuses->get($statusId);

                if (! $status || (int) $status->days <= 0) {
                    continue;
                }

                $start = $events[$index]['at'];
                $end = $events[$index + 1]['at'];
                $actualDays = max(0, $start->diffInMinutes($end) / 1440);
                $total++;

                if ($actualDays <= (float) $status->days) {
                    $compliant++;
                }
            }
        }

        return [
            'compliance_rate' => $total > 0 ? round(($compliant / $total) * 100, 1) : 0.0,
            'compliant_periods' => $compliant,
            'total_periods' => $total,
        ];
    }

    /**
     * @param  Collection<int, OperationStatus>  $statuses
     * @return array{
     *     start_id: int,
     *     end_ids: list<int>,
     *     selected_end_id: int|null,
     *     start_name: string,
     *     end_name: string
     * }
     */
    private function resolveLeadTimeBounds(
        Collection $statuses,
        ?int $fromStatusId,
        ?int $toStatusId,
    ): array {
        $ordered = $statuses->sortBy('sort_order')->values();

        $startId = ($fromStatusId !== null && $statuses->has($fromStatusId))
            ? $fromStatusId
            : (int) ($ordered->first()?->id ?? 0);

        $endStatuses = $statuses->where('is_end', true);
        $defaultEndIds = $endStatuses->isNotEmpty()
            ? $endStatuses->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
            : [(int) ($ordered->last()?->id ?? 0)];

        $defaultEndIds = array_values(array_filter($defaultEndIds));

        if ($toStatusId !== null && $statuses->has($toStatusId)) {
            $endIds = [$toStatusId];
            $selectedEndId = $toStatusId;
            $endName = (string) ($statuses->get($toStatusId)?->name ?? __('dobs.na'));
        } else {
            $endIds = $defaultEndIds;
            $selectedEndId = null;
            $endName = $endStatuses->isNotEmpty()
                ? $endStatuses->pluck('name')->implode(' / ')
                : (string) ($ordered->last()?->name ?? __('dobs.na'));
        }

        return [
            'start_id' => $startId,
            'end_ids' => $endIds,
            'selected_end_id' => $selectedEndId,
            'start_name' => (string) ($statuses->get($startId)?->name ?? __('dobs.na')),
            'end_name' => $endName,
        ];
    }

    /**
     * @param  array<int, list<array{status_id: int, at: Carbon}>>  $timelines
     * @param  list<int>  $endStatusIds
     * @param  Collection<int, int>  $operationIds
     * @return array{average_days: float, sample_count: int}
     */
    private function calculateLeadTimes(
        array $timelines,
        int $startStatusId,
        array $endStatusIds,
        Collection $operationIds,
    ): array {
        if ($operationIds->isEmpty() || $endStatusIds === []) {
            return ['average_days' => 0.0, 'sample_count' => 0];
        }

        $createdAtByOperation = Operation::query()
            ->whereIn('id', $operationIds)
            ->pluck('created_at', 'id');

        $leadTimes = [];

        foreach ($operationIds as $operationId) {
            $events = collect($timelines[$operationId] ?? [])->sortBy('at')->values();
            $startAt = null;

            foreach ($events as $event) {
                if ($event['status_id'] === $startStatusId) {
                    $startAt = $event['at'];
                    break;
                }
            }

            if ($startAt === null) {
                $startAt = $createdAtByOperation->get($operationId);
            }

            $endAt = null;

            foreach ($events as $event) {
                if (in_array($event['status_id'], $endStatusIds, true)) {
                    $endAt = $event['at'];
                    break;
                }
            }

            if ($startAt && $endAt && $endAt->greaterThan($startAt)) {
                $leadTimes[] = $startAt->diffInMinutes($endAt) / 1440;
            }
        }

        if ($leadTimes === []) {
            return ['average_days' => 0.0, 'sample_count' => 0];
        }

        return [
            'average_days' => round(array_sum($leadTimes) / count($leadTimes), 1),
            'sample_count' => count($leadTimes),
        ];
    }

    /**
     * @return list<array{supplier_id: int, supplier_name: string, avg_days: float, operations_count: int}>
     */
    private function supplierPrintingTurnaround($operationsQuery, array $timelines, int $printingStatusId): array
    {
        if ($printingStatusId <= 0) {
            return [];
        }

        $operations = (clone $operationsQuery)
            ->whereNotNull('printing_supplier_id')
            ->with('printingSupplier:id,name')
            ->get(['id', 'printing_supplier_id']);

        $buckets = [];

        foreach ($operations as $operation) {
            $events = collect($timelines[$operation->id] ?? [])->sortBy('at')->values();
            $dwell = $this->dwellInStatus($events, $printingStatusId);

            if ($dwell === null) {
                continue;
            }

            $supplierId = (int) $operation->printing_supplier_id;
            $buckets[$supplierId]['name'] = $operation->printingSupplier?->name ?? __('dobs.na');
            $buckets[$supplierId]['days'][] = $dwell;
        }

        return collect($buckets)
            ->map(function (array $bucket, int $supplierId): array {
                $days = $bucket['days'];

                return [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $bucket['name'],
                    'avg_days' => round(array_sum($days) / count($days), 1),
                    'operations_count' => count($days),
                ];
            })
            ->sortBy('avg_days')
            ->values()
            ->all();
    }

    /**
     * @return list<array{supplier_id: int, supplier_name: string, repeat_rate: float, avg_prep_days: float, operations_count: int}>
     */
    private function ctpSupplierEfficiency($operationsQuery, array $timelines, int $notStartedStatusId): array
    {
        $operations = (clone $operationsQuery)
            ->whereNotNull('ctp_supplier_id')
            ->with('ctpSupplier:id,name')
            ->get(['id', 'ctp_supplier_id', 'stencil']);

        $buckets = [];

        foreach ($operations as $operation) {
            $supplierId = (int) $operation->ctp_supplier_id;
            $buckets[$supplierId]['name'] = $operation->ctpSupplier?->name ?? __('dobs.na');
            $buckets[$supplierId]['total'] = ($buckets[$supplierId]['total'] ?? 0) + 1;

            if ($operation->stencil?->value === 'repeat') {
                $buckets[$supplierId]['repeat'] = ($buckets[$supplierId]['repeat'] ?? 0) + 1;
            }

            $events = collect($timelines[$operation->id] ?? [])->sortBy('at')->values();
            $prepDays = $this->dwellInStatus($events, $notStartedStatusId);

            if ($prepDays !== null) {
                $buckets[$supplierId]['prep_days'][] = $prepDays;
            }
        }

        return collect($buckets)
            ->map(function (array $bucket, int $supplierId): array {
                $total = (int) ($bucket['total'] ?? 0);
                $repeat = (int) ($bucket['repeat'] ?? 0);
                $prepDays = $bucket['prep_days'] ?? [];

                return [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $bucket['name'],
                    'repeat_rate' => $total > 0 ? round(($repeat / $total) * 100, 1) : 0.0,
                    'avg_prep_days' => $prepDays !== []
                        ? round(array_sum($prepDays) / count($prepDays), 1)
                        : 0.0,
                    'operations_count' => $total,
                ];
            })
            ->sortByDesc('operations_count')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array{status_id: int, at: Carbon}>  $events
     */
    private function dwellInStatus(Collection $events, int $statusId): ?float
    {
        for ($index = 0; $index < $events->count(); $index++) {
            if ((int) $events[$index]['status_id'] !== $statusId) {
                continue;
            }

            $start = $events[$index]['at'];
            $end = $events[$index + 1]['at'] ?? now();

            return max(0, $start->diffInMinutes($end) / 1440);
        }

        return null;
    }

    /**
     * @return array{waste_rate: float, yield_rate: float, sample_count: int}
     */
    private function materialWasteStats($offsetQuery): array
    {
        $operations = (clone $offsetQuery)
            ->whereNotNull('pull_count')
            ->whereNotNull('quantity_per_sheet')
            ->where('pull_count', '>', 0)
            ->get(['quantity', 'pull_count', 'quantity_per_sheet']);

        $wasteRates = [];

        foreach ($operations as $operation) {
            $actual = (int) $operation->pull_count * (int) $operation->quantity_per_sheet;
            $required = (int) ($operation->quantity ?? 0);

            if ($actual <= 0 || $required <= 0) {
                continue;
            }

            $wasteRates[] = max(0, (($actual - $required) / $actual) * 100);
        }

        if ($wasteRates === []) {
            return ['waste_rate' => 0.0, 'yield_rate' => 100.0, 'sample_count' => 0];
        }

        $avgWaste = array_sum($wasteRates) / count($wasteRates);

        return [
            'waste_rate' => round($avgWaste, 1),
            'yield_rate' => round(max(0, 100 - $avgWaste), 1),
            'sample_count' => count($wasteRates),
        ];
    }

    /**
     * @return array{rate: float, negative_count: int, total_count: int}
     */
    private function stockAnomalyRate(): array
    {
        $total = Item::count();
        $negative = Item::where('stock', '<', 0)->count();

        return [
            'rate' => $total > 0 ? round(($negative / $total) * 100, 1) : 0.0,
            'negative_count' => $negative,
            'total_count' => $total,
        ];
    }

    /**
     * @return list<array{paper_type_id: int|null, paper_type_name: string, operations_count: int, daily_avg: float}>
     */
    private function paperMaterialTurnover($operationsQuery, int $periodDays): array
    {
        return (clone $operationsQuery)
            ->whereNotNull('paper_type_id')
            ->leftJoin('paper_types', 'operations.paper_type_id', '=', 'paper_types.id')
            ->select(
                'operations.paper_type_id',
                'paper_types.name as paper_type_name',
                DB::raw('COUNT(operations.id) as operations_count'),
            )
            ->groupBy('operations.paper_type_id', 'paper_types.name')
            ->orderByDesc('operations_count')
            ->get()
            ->map(fn ($row) => [
                'paper_type_id' => $row->paper_type_id ? (int) $row->paper_type_id : null,
                'paper_type_name' => $row->paper_type_name ?? __('dobs.report_unspecified_paper_type'),
                'operations_count' => (int) $row->operations_count,
                'daily_avg' => round(((int) $row->operations_count) / $periodDays, 2),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{rate: float, active_clients: int, returning_clients: int}
     */
    private function clientRetentionRate($operationsQuery): array
    {
        $clientCounts = (clone $operationsQuery)
            ->whereNotNull('client_id')
            ->select('client_id', DB::raw('COUNT(*) as operations_count'))
            ->groupBy('client_id')
            ->pluck('operations_count', 'client_id');

        $active = $clientCounts->count();
        $returning = $clientCounts->filter(fn ($count) => (int) $count > 1)->count();

        return [
            'rate' => $active > 0 ? round(($returning / $active) * 100, 1) : 0.0,
            'active_clients' => $active,
            'returning_clients' => $returning,
        ];
    }

    /**
     * @return array{revenue: float, cost: float, profit: float, margin: float}
     */
    private function orderProfitability($operationsQuery): array
    {
        $operations = (clone $operationsQuery)
            ->with(['item:id,price', 'service1:id,price', 'service2:id,price', 'service3:id,price'])
            ->get([
                'id', 'total_amount', 'quantity', 'item_id',
                'service_1_id', 'service_2_id', 'service_3_id',
            ]);

        $revenue = 0.0;
        $cost = 0.0;

        foreach ($operations as $operation) {
            $recordedRevenue = (float) $operation->total_amount;

            if ($recordedRevenue > 0) {
                $revenue += $recordedRevenue;
            } else {
                $itemRevenue = (float) ($operation->item?->price ?? 0) * (int) ($operation->quantity ?? 0);
                $serviceRevenue = (float) ($operation->service1?->price ?? 0)
                    + (float) ($operation->service2?->price ?? 0)
                    + (float) ($operation->service3?->price ?? 0);
                $revenue += $itemRevenue + $serviceRevenue;
            }

            $cost += (float) ($operation->service1?->price ?? 0)
                + (float) ($operation->service2?->price ?? 0)
                + (float) ($operation->service3?->price ?? 0);
        }

        $profit = $revenue - $cost;

        return [
            'revenue' => round($revenue, 2),
            'cost' => round($cost, 2),
            'profit' => round($profit, 2),
            'margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0,
        ];
    }

    /**
     * @return list<array{item_id: int|null, item_name: string, operations_count: int, total_quantity: int}>
     */
    private function topSellingItems($operationsQuery): array
    {
        return (clone $operationsQuery)
            ->whereNotNull('item_id')
            ->leftJoin('items', 'operations.item_id', '=', 'items.id')
            ->select(
                'operations.item_id',
                'items.name as item_name',
                DB::raw('COUNT(operations.id) as operations_count'),
                DB::raw('COALESCE(SUM(operations.quantity), 0) as total_quantity'),
            )
            ->groupBy('operations.item_id', 'items.name')
            ->orderByDesc('operations_count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'item_id' => $row->item_id ? (int) $row->item_id : null,
                'item_name' => $row->item_name ?? __('dobs.report_unspecified_item'),
                'operations_count' => (int) $row->operations_count,
                'total_quantity' => (int) $row->total_quantity,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, int>  $operationIds
     * @return array{rate: float, modified_count: int}
     */
    private function orderModificationRate(Collection $operationIds, int $totalOperations): array
    {
        if ($totalOperations === 0) {
            return ['rate' => 0.0, 'modified_count' => 0];
        }

        $modifiedCount = OperationLog::query()
            ->whereIn('operation_id', $operationIds)
            ->where('action', OperationLog::ACTION_UPDATED)
            ->distinct('operation_id')
            ->count('operation_id');

        return [
            'rate' => round(($modifiedCount / $totalOperations) * 100, 1),
            'modified_count' => $modifiedCount,
        ];
    }

    /**
     * @return array{rate: float, failed: int, total: int}
     */
    private function jobFailureRate(): array
    {
        $failed = (int) DB::table('failed_jobs')->count();
        $pending = (int) DB::table('jobs')->count();
        $total = $failed + $pending;

        return [
            'rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0.0,
            'failed' => $failed,
            'total' => $total,
        ];
    }
}
