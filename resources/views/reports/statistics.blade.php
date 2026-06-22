@extends('layouts.app')

@section('title', __('dobs.report_statistics'))

@section('header_title', __('dobs.report_statistics'))
@section('header_subtitle', __('dobs.report_statistics_subtitle'))

@section('styles')
<style>
    .stats-filter-card {
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
    }

    .stats-filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 0.75rem;
    }

    .stats-filter-form .form-group {
        margin-bottom: 0;
        min-width: 160px;
    }

    .stats-period-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(6, 182, 212, 0.12);
        color: var(--color-secondary);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .stats-section {
        margin-top: 1.5rem;
    }

    .stats-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0 0 0.85rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .stats-section-title i {
        color: var(--color-secondary);
    }

    .stats-kpi-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 1rem;
    }

    .stats-kpi-card {
        grid-column: span 12;
        padding: 1rem;
    }

    @media (min-width: 768px) {
        .stats-kpi-card {
            grid-column: span 6;
        }
    }

    @media (min-width: 1200px) {
        .stats-kpi-card {
            grid-column: span 4;
        }

        .stats-kpi-card--wide {
            grid-column: span 6;
        }
    }

    .stats-kpi-label {
        display: block;
        font-size: 0.78rem;
        color: var(--text-secondary);
        margin-bottom: 0.35rem;
    }

    .stats-kpi-value {
        display: block;
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .stats-kpi-hint {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .stats-charts-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .stats-chart-card {
        grid-column: span 12;
        padding: 1rem;
    }

    @media (min-width: 992px) {
        .stats-chart-card--half {
            grid-column: span 6;
        }
    }

    .stats-chart-wrap {
        position: relative;
        height: 280px;
        margin-top: 0.5rem;
    }

    .stats-table-wrap {
        margin-top: 1rem;
        overflow-x: auto;
    }

    .stats-empty {
        padding: 1.25rem;
        text-align: center;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="glass-card stats-filter-card">
    <form method="GET" action="{{ route('reports.statistics') }}" class="stats-filter-form">
        <div class="form-group">
            <label class="form-label">{{ __('dobs.date_from') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('dobs.date_to') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" required>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-filter"></i> {{ __('dobs.apply_filters') }}
        </button>
        <span class="stats-period-badge">
            <i class="fa-regular fa-calendar"></i>
            {{ __('dobs.report_date_range', ['from' => $stats['period']['from'], 'to' => $stats['period']['to']]) }}
            ({{ __('dobs.stats_period_days', ['days' => $stats['period']['days']]) }})
        </span>
    </form>
</div>

{{-- 1. Operational & Production --}}
<section class="stats-section">
    <h2 class="stats-section-title">
        <i class="fa-solid fa-industry"></i>
        {{ __('dobs.stats_section_operational') }}
    </h2>
    <div class="stats-kpi-grid">
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_lead_time') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['operational']['avg_lead_time_days'], 1) }} {{ __('dobs.stats_days_unit') }}</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_lead_time_hint', ['count' => $stats['operational']['completed_lead_samples']]) }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_sop_compliance') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['operational']['sop_compliance_rate'], 1) }}%</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_sop_compliance_hint', ['compliant' => $stats['operational']['sop_compliant_periods'], 'total' => $stats['operational']['sop_total_periods']]) }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_throughput_pulls') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['operational']['throughput_pulls_per_day'], 2) }}</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_throughput_pulls_hint') }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_throughput_offset') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['operational']['throughput_offset_output_per_day'], 2) }}</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_throughput_offset_hint', ['total' => number_format($stats['operational']['offset_output'])]) }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_throughput_general') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['operational']['throughput_general_qty_per_day'], 2) }}</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_throughput_general_hint', ['total' => number_format($stats['operational']['general_output'])]) }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_total_operations') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['operational']['total_operations']) }}</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_total_operations_hint') }}</span>
        </div>
    </div>
</section>

{{-- 2. Supplier KPIs --}}
<section class="stats-section">
    <h2 class="stats-section-title">
        <i class="fa-solid fa-truck-field"></i>
        {{ __('dobs.stats_section_supplier') }}
    </h2>
    <div class="stats-charts-grid">
        <div class="glass-card stats-chart-card stats-chart-card--half">
            <h3 class="card-title">{{ __('dobs.stats_chart_supplier_turnaround') }}</h3>
            <div class="stats-chart-wrap">
                <canvas id="statsSupplierTurnaroundChart"></canvas>
            </div>
            @if (empty($stats['chart']['supplier_turnaround']))
                <div class="stats-empty">{{ __('dobs.report_no_data') }}</div>
            @endif
        </div>
        <div class="glass-card stats-chart-card stats-chart-card--half">
            <h3 class="card-title">{{ __('dobs.stats_chart_ctp_efficiency') }}</h3>
            <div class="stats-chart-wrap">
                <canvas id="statsCtpEfficiencyChart"></canvas>
            </div>
            @if (empty($stats['chart']['ctp_repeat_rate']))
                <div class="stats-empty">{{ __('dobs.report_no_data') }}</div>
            @endif
        </div>
    </div>
    @if (! empty($stats['supplier']['printing_turnaround']))
        <div class="glass-card stats-table-wrap">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __('dobs.col_supplier') }}</th>
                        <th>{{ __('dobs.stats_col_avg_days') }}</th>
                        <th>{{ __('dobs.stats_col_operations') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stats['supplier']['printing_turnaround'] as $row)
                        <tr>
                            <td>{{ $row['supplier_name'] }}</td>
                            <td>{{ number_format($row['avg_days'], 1) }}</td>
                            <td>{{ $row['operations_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

{{-- 3. Inventory & Material --}}
<section class="stats-section">
    <h2 class="stats-section-title">
        <i class="fa-solid fa-boxes-stacked"></i>
        {{ __('dobs.stats_section_inventory') }}
    </h2>
    <div class="stats-kpi-grid">
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_waste_rate') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['inventory']['waste_rate'], 1) }}%</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_waste_rate_hint', ['count' => $stats['inventory']['waste_samples']]) }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_yield_rate') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['inventory']['yield_rate'], 1) }}%</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_yield_rate_hint') }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_stock_anomaly') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['inventory']['stock_anomaly_rate'], 1) }}%</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_stock_anomaly_hint', ['negative' => $stats['inventory']['negative_stock_items'], 'total' => $stats['inventory']['total_items']]) }}</span>
        </div>
    </div>
    <div class="stats-charts-grid">
        <div class="glass-card stats-chart-card">
            <h3 class="card-title">{{ __('dobs.stats_chart_paper_turnover') }}</h3>
            <div class="stats-chart-wrap">
                <canvas id="statsPaperTurnoverChart"></canvas>
            </div>
            @if (empty($stats['chart']['paper_turnover']))
                <div class="stats-empty">{{ __('dobs.report_no_data') }}</div>
            @endif
        </div>
    </div>
</section>

{{-- 4. Commercial & Client --}}
<section class="stats-section">
    <h2 class="stats-section-title">
        <i class="fa-solid fa-handshake"></i>
        {{ __('dobs.stats_section_commercial') }}
    </h2>
    <div class="stats-kpi-grid">
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_client_retention') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['commercial']['client_retention_rate'], 1) }}%</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_client_retention_hint', ['returning' => $stats['commercial']['returning_clients'], 'active' => $stats['commercial']['active_clients']]) }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_revenue') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['commercial']['total_revenue'], 2) }} {{ __('dobs.currency') }}</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_revenue_hint') }}</span>
        </div>
        <div class="glass-card stats-kpi-card">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_profit') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['commercial']['net_profit'], 2) }} {{ __('dobs.currency') }}</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_profit_hint', ['margin' => number_format($stats['commercial']['profit_margin'], 1)]) }}</span>
        </div>
    </div>
    <div class="stats-charts-grid">
        <div class="glass-card stats-chart-card">
            <h3 class="card-title">{{ __('dobs.stats_chart_top_items') }}</h3>
            <div class="stats-chart-wrap">
                <canvas id="statsTopItemsChart"></canvas>
            </div>
            @if (empty($stats['chart']['top_items']))
                <div class="stats-empty">{{ __('dobs.report_no_data') }}</div>
            @endif
        </div>
    </div>
</section>

{{-- 5. System & Data Quality --}}
<section class="stats-section">
    <h2 class="stats-section-title">
        <i class="fa-solid fa-database"></i>
        {{ __('dobs.stats_section_system') }}
    </h2>
    <div class="stats-kpi-grid">
        <div class="glass-card stats-kpi-card stats-kpi-card--wide">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_modification_rate') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['system']['modification_rate'], 1) }}%</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_modification_rate_hint', ['count' => $stats['system']['modified_operations'], 'total' => $stats['operational']['total_operations']]) }}</span>
        </div>
        <div class="glass-card stats-kpi-card stats-kpi-card--wide">
            <span class="stats-kpi-label">{{ __('dobs.stats_kpi_job_failure') }}</span>
            <span class="stats-kpi-value">{{ number_format($stats['system']['job_failure_rate'], 1) }}%</span>
            <span class="stats-kpi-hint">{{ __('dobs.stats_kpi_job_failure_hint', ['failed' => $stats['system']['failed_jobs'], 'total' => $stats['system']['total_jobs']]) }}</span>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@php
    $chartLabels = [
        'operations_count' => __('dobs.chart_operations_count'),
        'avg_days' => __('dobs.stats_col_avg_days'),
        'repeat_rate' => __('dobs.stats_chart_repeat_rate'),
        'daily_avg' => __('dobs.stats_chart_daily_avg'),
    ];
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    var chartData = @json($stats['chart']);
    var labels = @json($chartLabels);
    var textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim() || '#94a3b8';
    var gridColor = 'rgba(148, 163, 184, 0.15)';
    var palette = ['#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#ec4899', '#14b8a6'];

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

    var supplierRows = chartData.supplier_turnaround || [];
    if (supplierRows.length && document.getElementById('statsSupplierTurnaroundChart')) {
        new Chart(document.getElementById('statsSupplierTurnaroundChart'), {
            type: 'bar',
            data: {
                labels: supplierRows.map(function (row) { return row.supplier_name; }),
                datasets: [{
                    label: labels.avg_days,
                    data: supplierRows.map(function (row) { return row.avg_days; }),
                    backgroundColor: '#06b6d4',
                    borderRadius: 8,
                    maxBarThickness: 42,
                }],
            },
            options: Object.assign(baseOptions(), {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
                },
            }),
        });
    }

    var ctpRows = chartData.ctp_repeat_rate || [];
    if (ctpRows.length && document.getElementById('statsCtpEfficiencyChart')) {
        new Chart(document.getElementById('statsCtpEfficiencyChart'), {
            type: 'bar',
            data: {
                labels: ctpRows.map(function (row) { return row.supplier_name; }),
                datasets: [{
                    label: labels.repeat_rate,
                    data: ctpRows.map(function (row) { return row.repeat_rate; }),
                    backgroundColor: '#f59e0b',
                    borderRadius: 8,
                    maxBarThickness: 42,
                }],
            },
            options: Object.assign(baseOptions(), {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { display: false } },
                    y: { beginAtZero: true, max: 100, ticks: { color: textColor }, grid: { color: gridColor } },
                },
            }),
        });
    }

    var paperRows = chartData.paper_turnover || [];
    if (paperRows.length && document.getElementById('statsPaperTurnoverChart')) {
        new Chart(document.getElementById('statsPaperTurnoverChart'), {
            type: 'bar',
            data: {
                labels: paperRows.map(function (row) { return row.paper_type_name; }),
                datasets: [{
                    label: labels.daily_avg,
                    data: paperRows.map(function (row) { return row.daily_avg; }),
                    backgroundColor: '#10b981',
                    borderRadius: 8,
                    maxBarThickness: 42,
                }],
            },
            options: Object.assign(baseOptions(), {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } },
                },
            }),
        });
    }

    var topItems = chartData.top_items || [];
    if (topItems.length && document.getElementById('statsTopItemsChart')) {
        new Chart(document.getElementById('statsTopItemsChart'), {
            type: 'bar',
            data: {
                labels: topItems.map(function (row) { return row.item_name; }),
                datasets: [{
                    label: labels.operations_count,
                    data: topItems.map(function (row) { return row.operations_count; }),
                    backgroundColor: palette,
                    borderRadius: 8,
                    maxBarThickness: 42,
                }],
            },
            options: Object.assign(baseOptions(), {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } },
                },
            }),
        });
    }
})();
</script>
@endsection
