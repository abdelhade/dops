@extends('layouts.app')

@section('title', __('dobs.dashboard_title'))

@section('header_title', __('dobs.dashboard_title'))
@section('header_subtitle', __('dobs.dashboard_subtitle'))

@section('styles')
<style>
    .dashboard-charts-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .dashboard-chart-card {
        grid-column: span 12;
    }

    .dashboard-chart-card--wide {
        grid-column: span 12;
    }

    .dashboard-chart-card--half {
        grid-column: span 12;
    }

    @media (min-width: 992px) {
        .dashboard-chart-card--wide {
            grid-column: span 8;
        }

        .dashboard-chart-card--side {
            grid-column: span 4;
        }

        .dashboard-chart-card--half {
            grid-column: span 6;
        }
    }

    .dashboard-chart-wrap {
        position: relative;
        height: 300px;
        margin-top: 0.75rem;
    }

    .dashboard-chart-wrap--compact {
        height: 260px;
    }

    .dashboard-chart-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-secondary);
        font-size: 0.9rem;
        text-align: center;
        padding: 1rem;
    }

    .dashboard-bottom-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    @media (min-width: 992px) {
        .dashboard-bottom-grid {
            grid-template-columns: 2fr 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="stats-grid">
    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_total_earning') }}</span>
            <span class="stat-value">{{ number_format($stats['total_sales'], 2) }} {{ __('dobs.currency') }}</span>
        </div>
        <div class="stat-icon success">
            <i class="fa-solid fa-coins"></i>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_operations') }}</span>
            <span class="stat-value">{{ $stats['operations_count'] }}</span>
        </div>
        <div class="stat-icon primary">
            <i class="fa-solid fa-arrows-spin"></i>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_total_items') }}</span>
            <span class="stat-value">{{ $stats['items_count'] }}</span>
        </div>
        <div class="stat-icon secondary">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_suppliers') }}</span>
            <span class="stat-value">{{ $stats['suppliers_count'] }}</span>
        </div>
        <div class="stat-icon warning">
            <i class="fa-solid fa-truck-field"></i>
        </div>
    </div>
</div>

<div class="dashboard-charts-grid">
    <div class="glass-card dashboard-chart-card dashboard-chart-card--wide">
        <div class="card-header-flex">
            <h2 class="card-title">{{ __('dobs.chart_operations_trend') }}</h2>
        </div>
        <div class="dashboard-chart-wrap">
            <canvas id="dobsDashboardOperationsTrendChart" aria-hidden="true"></canvas>
            <div id="dobsDashboardOperationsTrendEmpty" class="dashboard-chart-empty" hidden>
                {{ __('dobs.chart_no_data') }}
            </div>
        </div>
    </div>

    <div class="glass-card dashboard-chart-card dashboard-chart-card--side">
        <div class="card-header-flex">
            <h2 class="card-title">{{ __('dobs.chart_operations_by_status') }}</h2>
        </div>
        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
            <canvas id="dobsDashboardOperationsStatusChart" aria-hidden="true"></canvas>
            <div id="dobsDashboardOperationsStatusEmpty" class="dashboard-chart-empty" hidden>
                {{ __('dobs.chart_no_data') }}
            </div>
        </div>
    </div>

    <div class="glass-card dashboard-chart-card dashboard-chart-card--half">
        <div class="card-header-flex">
            <h2 class="card-title">{{ __('dobs.chart_items_by_category') }}</h2>
        </div>
        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
            <canvas id="dobsDashboardItemsCategoryChart" aria-hidden="true"></canvas>
            <div id="dobsDashboardItemsCategoryEmpty" class="dashboard-chart-empty" hidden>
                {{ __('dobs.chart_no_data') }}
            </div>
        </div>
    </div>

    <div class="glass-card dashboard-chart-card dashboard-chart-card--half">
        <div class="card-header-flex">
            <h2 class="card-title">{{ __('dobs.chart_paper_types_usage') }}</h2>
        </div>
        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
            <canvas id="dobsDashboardPaperTypesChart" aria-hidden="true"></canvas>
            <div id="dobsDashboardPaperTypesEmpty" class="dashboard-chart-empty" hidden>
                {{ __('dobs.chart_no_data') }}
            </div>
        </div>
    </div>
</div>

<div class="dashboard-bottom-grid">
    <div class="glass-card">
        <div class="card-header-flex">
            <h2 class="card-title">{{ __('dobs.recent_operations') }}</h2>
            <a href="{{ route('operations.index') }}" class="btn btn-secondary btn-sm">{{ __('dobs.view_all') }}</a>
        </div>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __('dobs.col_op_number') }}</th>
                        <th>{{ __('dobs.col_date') }}</th>
                        <th>{{ __('dobs.col_item') }}</th>
                        <th>{{ __('dobs.col_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_operations as $op)
                        <tr>
                            <td>
                                <a href="{{ route('operations.show', $op->id) }}" style="color: var(--color-secondary); font-weight:600; text-decoration:none;">
                                    {{ $op->operation_number }}
                                </a>
                            </td>
                            <td>{{ $op->operation_date?->format('Y-m-d') ?? $op->operation_date }}</td>
                            <td>{{ $op->item?->name ?? __('dobs.dash') }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $op->operationStatus?->color ?? '#6c757d' }}; color: white;">
                                    {{ $op->operationStatus?->name ?? __('dobs.dash') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fa-solid fa-receipt"></i>
                                {{ __('dobs.no_recent_operations') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card" style="border-color: rgba(239, 68, 68, 0.25);">
        <div class="card-header-flex">
            <h2 class="card-title" style="color: #fca5a5;">{{ __('dobs.low_stock_warning') }}</h2>
            <span class="badge badge-danger">{{ __('dobs.alert') }}</span>
        </div>
        <div>
            @forelse($low_stock_items as $item)
                <div class="list-item-flex">
                    <div class="item-details">
                        <a href="{{ route('items.show', $item->id) }}" style="color: var(--text-primary); text-decoration:none;" class="item-title">
                            {{ $item->name }}
                        </a>
                        <span class="item-subtitle">{{ __('dobs.col_sku') }}: {{ $item->sku }}</span>
                    </div>
                    <span style="font-weight: 700; color: var(--color-danger);">{{ __('dobs.left_stock', ['count' => $item->stock]) }}</span>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-circle-check" style="color: var(--color-success);"></i>
                    {{ __('dobs.healthy_stock') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    var chartData = @json($chartData);
    var palette = ['#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#ec4899', '#14b8a6'];
    var textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim() || '#94a3b8';
    var gridColor = 'rgba(148, 163, 184, 0.15)';

    function hasValues(rows, key) {
        return Array.isArray(rows) && rows.some(function (row) {
            return Number(row[key] || 0) > 0;
        });
    }

    function showEmpty(canvasId, emptyId, isEmpty) {
        var canvas = document.getElementById(canvasId);
        var empty = document.getElementById(emptyId);

        if (!canvas || !empty) {
            return;
        }

        canvas.hidden = isEmpty;
        empty.hidden = !isEmpty;
    }

    function baseOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: textColor,
                        font: { family: 'Cairo, sans-serif' },
                    },
                },
            },
        };
    }

    var monthly = chartData.monthly_operations || [];
    showEmpty('dobsDashboardOperationsTrendChart', 'dobsDashboardOperationsTrendEmpty', !hasValues(monthly, 'count') && !hasValues(monthly, 'revenue'));

    if (hasValues(monthly, 'count') || hasValues(monthly, 'revenue')) {
        new Chart(document.getElementById('dobsDashboardOperationsTrendChart'), {
            type: 'line',
            data: {
                labels: monthly.map(function (row) { return row.label; }),
                datasets: [
                    {
                        label: chartData.labels.operations_count,
                        data: monthly.map(function (row) { return row.count; }),
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.15)',
                        tension: 0.35,
                        fill: true,
                        yAxisID: 'y',
                    },
                    {
                        label: chartData.labels.revenue + ' (' + chartData.labels.currency + ')',
                        data: monthly.map(function (row) { return row.revenue; }),
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.12)',
                        tension: 0.35,
                        fill: false,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: Object.assign(baseOptions(), {
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: {
                        ticks: { color: textColor },
                        grid: { color: gridColor },
                    },
                    y: {
                        position: 'right',
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor },
                    },
                    y1: {
                        position: 'left',
                        beginAtZero: true,
                        ticks: { color: textColor },
                        grid: { drawOnChartArea: false },
                    },
                },
            }),
        });
    }

    var byStatus = chartData.operations_by_status || [];
    showEmpty('dobsDashboardOperationsStatusChart', 'dobsDashboardOperationsStatusEmpty', !hasValues(byStatus, 'count'));

    if (hasValues(byStatus, 'count')) {
        new Chart(document.getElementById('dobsDashboardOperationsStatusChart'), {
            type: 'doughnut',
            data: {
                labels: byStatus.map(function (row) { return row.label; }),
                datasets: [{
                    data: byStatus.map(function (row) { return row.count; }),
                    backgroundColor: byStatus.map(function (row) { return row.color; }),
                    borderWidth: 0,
                }],
            },
            options: Object.assign(baseOptions(), {
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { family: 'Cairo, sans-serif' },
                            boxWidth: 12,
                        },
                    },
                },
            }),
        });
    }

    var byCategory = chartData.items_by_category || [];
    showEmpty('dobsDashboardItemsCategoryChart', 'dobsDashboardItemsCategoryEmpty', !hasValues(byCategory, 'count'));

    if (hasValues(byCategory, 'count')) {
        new Chart(document.getElementById('dobsDashboardItemsCategoryChart'), {
            type: 'bar',
            data: {
                labels: byCategory.map(function (row) { return row.label; }),
                datasets: [{
                    label: chartData.labels.items_count,
                    data: byCategory.map(function (row) { return row.count; }),
                    backgroundColor: palette,
                    borderRadius: 8,
                    maxBarThickness: 42,
                }],
            },
            options: Object.assign(baseOptions(), {
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { color: textColor },
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor },
                    },
                },
            }),
        });
    }

    var paperTypes = chartData.paper_types_usage || [];
    showEmpty('dobsDashboardPaperTypesChart', 'dobsDashboardPaperTypesEmpty', !hasValues(paperTypes, 'count'));

    if (hasValues(paperTypes, 'count')) {
        new Chart(document.getElementById('dobsDashboardPaperTypesChart'), {
            type: 'bar',
            data: {
                labels: paperTypes.map(function (row) { return row.label; }),
                datasets: [{
                    label: chartData.labels.operations_count,
                    data: paperTypes.map(function (row) { return row.count; }),
                    backgroundColor: '#06b6d4',
                    borderRadius: 8,
                    maxBarThickness: 42,
                }],
            },
            options: Object.assign(baseOptions(), {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor },
                    },
                    y: {
                        ticks: { color: textColor },
                        grid: { display: false },
                    },
                },
            }),
        });
    }
})();
</script>
@endsection
