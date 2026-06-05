@extends('layouts.app')

@section('title', __('dobs.report_paper_materials_summary'))

@section('header_title', __('dobs.report_paper_materials_summary'))
@section('header_subtitle', __('dobs.report_paper_materials_summary_subtitle'))

@section('styles')
<style>
    .zanka-report-wrap {
        background: #fff;
        color: #000;
        border-radius: var(--radius-md);
        padding: 1rem;
    }

    .zanka-report-header {
        text-align: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #000;
    }

    .zanka-report-title {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0 0 0.35rem;
        color: #000;
    }

    .zanka-report-dates {
        font-size: 0.9rem;
        color: #333;
    }

    .zanka-report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.72rem;
        line-height: 1.35;
        table-layout: fixed;
        color: #000;
    }

    .zanka-report-table th,
    .zanka-report-table td {
        border: 1px solid #333;
        padding: 0.3rem 0.25rem;
        vertical-align: top;
        text-align: center;
        word-wrap: break-word;
        background: #fff;
        color: #000;
    }

    .zanka-report-table thead th {
        background: #e8e8e8;
        font-weight: 700;
        font-size: 0.68rem;
    }

    .zanka-report-table .col-date { width: 4.5rem; }
    .zanka-report-table .col-num { width: 1.6rem; }
    .zanka-report-table .col-statement { width: 7rem; text-align: right; }
    .zanka-report-table .col-ctp { width: 3.5rem; }
    .zanka-report-table .col-colors { width: 2.2rem; }
    .zanka-report-table .col-press { width: 3.5rem; }
    .zanka-report-table .col-paper { width: 7rem; text-align: right; }
    .zanka-report-table .col-size { width: 3.2rem; }
    .zanka-report-table .col-qty { width: 2.5rem; }
    .zanka-report-table .col-services { width: 5rem; text-align: right; }
    .zanka-report-table .col-supplier { width: 3rem; }
    .zanka-report-table .col-status { width: 1.8rem; }
    .zanka-report-table .col-serial { width: 3rem; }
    .zanka-report-table .col-notes { width: 6rem; text-align: right; }

    .zanka-report-table tbody td.col-statement,
    .zanka-report-table tbody td.col-paper,
    .zanka-report-table tbody td.col-services,
    .zanka-report-table tbody td.col-notes {
        text-align: right;
    }

    .zanka-summary-table {
        width: auto;
        min-width: 50%;
        margin-top: 1.25rem;
        margin-right: 0;
        margin-left: auto;
    }

    .zanka-summary-table th,
    .zanka-summary-table td {
        text-align: right;
    }

    .zanka-summary-table td:first-child,
    .zanka-summary-table th:first-child {
        text-align: right;
        min-width: 14rem;
    }

    .zanka-report-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 0.5rem;
        border-top: 1px solid #ccc;
        font-size: 0.7rem;
        color: #555;
    }

    @media print {
        @page {
            size: landscape;
            margin: 8mm;
        }

        body {
            background: white !important;
            color: black !important;
        }

        .sidebar, .no-print, .page-subtitle, .top-header, .top-header .header-actions, .alert {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .zanka-report-wrap {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }

        .zanka-report-table {
            font-size: 7pt;
        }

        .zanka-report-table th,
        .zanka-report-table td {
            padding: 2px 3px !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .zanka-report-table thead th {
            background: #e8e8e8 !important;
        }

        .zanka-report-table tr {
            page-break-inside: avoid;
        }

        .zanka-report-header {
            page-break-after: avoid;
        }
    }
</style>
@endsection

@section('content')
@php
    $hasActiveFilters = request()->filled('date_from') || request()->filled('date_to');
    $dateFrom = request('date_from');
    $dateTo = request('date_to');
    $dash = __('dobs.dash');

    if ($dateFrom && $dateTo) {
        $dateRangeLabel = __('dobs.report_date_range', ['from' => $dateFrom, 'to' => $dateTo]);
    } elseif ($dateFrom) {
        $dateRangeLabel = __('dobs.report_date_range', ['from' => $dateFrom, 'to' => $dateFrom]);
    } elseif ($dateTo) {
        $dateRangeLabel = __('dobs.report_date_range', ['from' => $dateTo, 'to' => $dateTo]);
    } else {
        $dateRangeLabel = __('dobs.report_date_range_all');
    }
@endphp

<div class="no-print" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
    <button type="button" class="btn btn-secondary" onclick="window.print()">
        <i class="fa-solid fa-print"></i> {{ __('dobs.print') }}
    </button>
</div>

<div class="operations-filters-card glass-card no-print">
    <button
        type="button"
        class="btn btn-secondary btn-sm operations-filters-toggle"
        id="report-filters-toggle"
        aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
        aria-controls="report-filters-panel"
    >
        <i class="fa-solid fa-filter"></i>
        <span>{{ __('dobs.filters') }}</span>
        @if ($hasActiveFilters)
            <span class="operations-filters-badge" aria-hidden="true"></span>
        @endif
        <i class="fa-solid fa-chevron-down operations-filters-chevron"></i>
    </button>

    <div
        id="report-filters-panel"
        class="operations-filters-panel"
        @unless($hasActiveFilters) hidden @endunless
    >
        <form method="GET" action="{{ route('reports.paper-materials-summary') }}" class="filters-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.date_from') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.date_to') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;">
                        <i class="fa-solid fa-filter"></i> {{ __('dobs.apply_filters') }}
                    </button>
                    <a href="{{ route('reports.paper-materials-summary') }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.clear_filters') }}">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="glass-card zanka-report-wrap">
    <div class="zanka-report-header">
        <h2 class="zanka-report-title">{{ __('dobs.report_zanka_list_title') }}</h2>
        <div class="zanka-report-dates">{{ $dateRangeLabel }}</div>
    </div>

    <div class="table-container" style="overflow-x: auto;">
        <table class="zanka-report-table">
            <thead>
                <tr>
                    <th class="col-date">{{ __('dobs.col_date') }}</th>
                    <th class="col-num">{{ __('dobs.report_col_row_num') }}</th>
                    <th class="col-statement">{{ __('dobs.report_col_statement') }}</th>
                    <th class="col-ctp">{{ __('dobs.operation_ctp') }}</th>
                    <th class="col-colors">{{ __('dobs.report_col_colors') }}</th>
                    <th class="col-press">{{ __('dobs.operation_printing_press') }}</th>
                    <th class="col-paper">{{ __('dobs.report_col_paper_type') }}</th>
                    <th class="col-size">{{ __('dobs.report_col_job_sheet_size') }}</th>
                    <th class="col-size">{{ __('dobs.report_col_zanka_size') }}</th>
                    <th class="col-qty">{{ __('dobs.report_col_pull_runs') }}</th>
                    <th class="col-qty">{{ __('dobs.report_col_total_pulls') }}</th>
                    <th class="col-qty">{{ __('dobs.report_col_qty_per_sheet') }}</th>
                    <th class="col-services">{{ __('dobs.report_col_services') }}</th>
                    <th class="col-supplier">{{ __('dobs.report_col_supplier') }}</th>
                    <th class="col-status">{{ __('dobs.report_col_status_short') }}</th>
                    <th class="col-serial">{{ __('dobs.report_col_serial') }}</th>
                    <th class="col-notes">{{ __('dobs.report_col_notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operations as $index => $op)
                    @php
                        $dimension = $op->reportPaperDimension();
                        $jobSizeLabel = $dimension ? $dimension . '-' : ($op->job_size !== null ? number_format((float) $op->job_size, 0) : '');
                        $sheetSizeLabel = $dimension ?? ($op->job_size !== null ? number_format((float) $op->job_size, 0) : '');
                        $services = $op->reportServicesLabel();
                        $totalPulls = $op->reportTotalPullQuantity();
                        $statusName = $op->operationStatus?->name ?? '';
                        $statusShort = $statusName !== '' ? mb_substr($statusName, 0, 1) : $dash;
                        $notes = trim((string) ($op->statement ?? $op->notes ?? ''));
                    @endphp
                    <tr>
                        <td class="col-date">{{ $op->operation_date?->format('Y-m-d') ?? $dash }}</td>
                        <td class="col-num">{{ $index + 1 }}</td>
                        <td class="col-statement">{{ $op->item?->name ?? $dash }}</td>
                        <td class="col-ctp">{{ $op->ctpSupplier?->name ?? $dash }}</td>
                        <td class="col-colors">{{ $op->color_count ?? $dash }}</td>
                        <td class="col-press">{{ $op->printingSupplier?->name ?? $dash }}</td>
                        <td class="col-paper">{{ $op->paperType?->name ?? $dash }}</td>
                        <td class="col-size">{{ $jobSizeLabel !== '' ? $jobSizeLabel : $dash }}</td>
                        <td class="col-size">{{ $sheetSizeLabel !== '' ? $sheetSizeLabel : $dash }}</td>
                        <td class="col-qty">{{ $op->pull_count ?? $dash }}</td>
                        <td class="col-qty">{{ $totalPulls ?? $dash }}</td>
                        <td class="col-qty">{{ $op->quantity_per_sheet ?? $dash }}</td>
                        <td class="col-services">{{ $services !== '' ? $services : $dash }}</td>
                        <td class="col-supplier">{{ $dash }}</td>
                        <td class="col-status">{{ $statusShort }}</td>
                        <td class="col-serial">{{ $op->operation_number }}</td>
                        <td class="col-notes">{{ $notes !== '' ? $notes : $dash }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="empty-state">
                            {{ __('dobs.report_no_data') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($rows->isNotEmpty())
        <table class="zanka-report-table zanka-summary-table">
            <thead>
                <tr>
                    <th>{{ __('dobs.report_paper_name') }}</th>
                    <th>{{ __('dobs.report_col_total_pulls') }}</th>
                    <th>{{ __('dobs.report_col_qty_per_sheet') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->paper_type_name }}</td>
                        <td>{{ number_format($row->total_pull_count) }}</td>
                        <td>{{ number_format($row->total_quantity_per_sheet) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>{{ __('dobs.report_totals') }}</th>
                    <th>{{ number_format($totals->total_pull_count) }}</th>
                    <th>{{ number_format($totals->total_quantity_per_sheet) }}</th>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="zanka-report-footer">
        <span>{{ __('dobs.report_footer_copyright') }}</span>
        <span>{{ now()->format('n/j/y, g:i A') }}</span>
        <span>{{ __('dobs.app_name') }}</span>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const toggle = document.getElementById('report-filters-toggle');
    const panel = document.getElementById('report-filters-panel');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', function () {
        const open = panel.hidden;
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.classList.toggle('is-open', open);
    });

    if (toggle.getAttribute('aria-expanded') === 'true') {
        toggle.classList.add('is-open');
    }
})();
</script>
@endsection
