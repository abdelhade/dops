@extends('layouts.app')

@section('title', __('dobs.report_general_operations_summary'))

@section('header_title', __('dobs.report_general_operations_summary'))
@section('header_subtitle', __('dobs.report_general_operations_summary_subtitle'))

@section('styles')
<style>
    .report-filters-card {
        margin-bottom: 0.65rem;
        padding: 0.55rem 0.75rem;
    }

    .report-filters-card .form-group {
        margin-bottom: 0;
    }

    .report-filters-card .form-label {
        font-size: 0.76rem;
        margin-bottom: 0.15rem;
    }

    .report-filters-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.45rem;
        padding-bottom: 0.35rem;
        border-bottom: 1px solid var(--border-color);
    }

    .report-filters-card-title {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .report-filters-card-title i {
        color: var(--color-secondary);
        font-size: 0.85rem;
    }

    .report-filters-card-hint {
        margin: 0;
        font-size: 0.72rem;
        color: var(--text-secondary);
    }

    .report-filters-primary {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        align-items: flex-end;
        justify-content: space-between;
    }

    .report-filters-primary-fields {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        flex: 1;
        min-width: min(100%, 520px);
    }

    .report-filter-field {
        margin-bottom: 0;
        min-width: 140px;
    }

    .report-filter-field-dates {
        width: 148px;
    }

    .report-filter-field-search {
        flex: 1;
        min-width: 180px;
    }

    .report-filter-search-wrap {
        position: relative;
    }

    .report-filter-search-wrap i {
        position: absolute;
        top: 50%;
        right: 0.85rem;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .report-filter-search-wrap .form-control {
        padding-right: 2.35rem;
    }

    .report-filters-actions {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .report-filters-actions .btn {
        padding: 0.3rem 0.55rem;
        font-size: 0.8rem;
    }

    .report-filters-advanced {
        margin-top: 0.4rem;
        border-top: 1px dashed var(--border-color);
        padding-top: 0.35rem;
    }

    .report-filters-advanced-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        list-style: none;
        user-select: none;
        padding: 0.1rem 0;
    }

    .report-filters-advanced-toggle::-webkit-details-marker {
        display: none;
    }

    .report-filters-advanced[open] .report-filters-advanced-toggle {
        color: var(--color-secondary);
        margin-bottom: 0.45rem;
    }

    .report-filters-advanced-chevron {
        font-size: 0.75rem;
        transition: transform 0.2s ease;
    }

    .report-filters-advanced[open] .report-filters-advanced-chevron {
        transform: rotate(180deg);
    }

    .report-filters-advanced-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.45rem;
    }

    .report-filter-field-wide {
        grid-column: 1 / -1;
    }

    .report-awaiting-card {
        text-align: center;
        padding: 3rem 1.5rem;
        border: 1px dashed var(--border-color);
    }

    .report-awaiting-card i {
        font-size: 2.5rem;
        color: var(--color-secondary);
        margin-bottom: 1rem;
        opacity: 0.85;
    }

    .report-awaiting-card p {
        margin: 0;
        color: var(--text-secondary);
        max-width: 34rem;
        margin-inline: auto;
        line-height: 1.7;
    }

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

    .zanka-report-table .col-serial { width: 3.2rem; }
    .zanka-report-table .col-date { width: 4.5rem; }
    .zanka-report-table .col-client { width: auto; white-space: nowrap; text-align: right; }
    .zanka-report-table .col-sales-order { width: 4.5rem; text-align: right; }
    .zanka-report-table .col-item { width: auto; white-space: nowrap; text-align: right; }
    .zanka-report-table .col-qty { width: 2.5rem; }
    .zanka-report-table .col-statement { width: 6rem; text-align: right; }
    .zanka-report-table .col-press { width: 3.5rem; }
    .zanka-report-table .col-ctp { width: 3rem; }
    .zanka-report-table .col-colors { width: 2.2rem; }
    .zanka-report-table .col-paper { width: 6rem; text-align: right; }
    .zanka-report-table .col-size { width: 3rem; }
    .zanka-report-table .col-pull { width: 2.5rem; }
    .zanka-report-table .col-qty-sheet { width: 2.8rem; }
    .zanka-report-table .col-service { width: 3.5rem; text-align: right; }
    .zanka-report-table .col-status { width: 3rem; }
    .zanka-report-table .col-notes { width: 5rem; text-align: right; }

    .zanka-report-table tbody td.col-client,
    .zanka-report-table tbody td.col-sales-order,
    .zanka-report-table tbody td.col-item,
    .zanka-report-table tbody td.col-statement,
    .zanka-report-table tbody td.col-paper,
    .zanka-report-table tbody td.col-service,
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

    @media (max-width: 768px) {
        .report-filters-primary {
            flex-direction: column;
            align-items: stretch;
        }

        .report-filters-actions {
            width: 100%;
        }

        .report-filters-actions .btn {
            flex: 1;
        }
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

        .sidebar, .no-print, .page-subtitle, .top-header, .top-header .header-actions, .alert, .report-filters-card, .report-awaiting-card {
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
    $filtersApplied = $filtersApplied ?? false;
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

<div class="glass-card report-filters-card no-print">
    <div class="report-filters-card-header">
        <h2 class="report-filters-card-title">
            <i class="fa-solid fa-filter" aria-hidden="true"></i>
            {{ __('dobs.report_filters_title') }}
        </h2>
        <p class="report-filters-card-hint">{{ __('dobs.report_filters_hint') }}</p>
    </div>

    @include('reports._filters', [
        'filterAction' => route('reports.general-operations-summary'),
        'clearFiltersUrl' => route('reports.general-operations-summary'),
        'showPrintButton' => $filtersApplied,
    ])
</div>

@if (! $filtersApplied)
    <div class="glass-card report-awaiting-card no-print">
        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
        <p>{{ __('dobs.report_awaiting_filters') }}</p>
    </div>
@else
    <div class="glass-card zanka-report-wrap">
        <div class="zanka-report-header">
            <h2 class="zanka-report-title">{{ __('dobs.report_general_list_title') }}</h2>
            <div class="zanka-report-dates">{{ $dateRangeLabel }}</div>
        </div>

        <div class="table-container" style="overflow-x: auto;">
            <table class="zanka-report-table">
                <thead>
                    <tr>
                        <th class="col-serial">{{ __('dobs.operation_serial') }}</th>
                        <th class="col-date">{{ __('dobs.col_date') }}</th>
                        <th class="col-client">{{ __('dobs.operation_client') }}</th>
                        <th class="col-sales-order">{{ __('dobs.operation_related_sales_order_number') }}</th>
                        <th class="col-item">{{ __('dobs.log_field_item_id') }}</th>
                        <th class="col-qty">{{ __('dobs.col_quantity') }}</th>
                        <th class="col-kind">{{ __('dobs.report_col_kind') }}</th>
                        <th class="col-stencil">{{ __('dobs.report_col_stencil') }}</th>
                        <th class="col-unit">{{ __('dobs.report_col_silk_unit') }}</th>
                        <th class="col-statement">{{ __('dobs.operation_statement') }}</th>
                        <th class="col-press">{{ __('dobs.operation_silk_supplier') }}</th>
                        <th class="col-status">{{ __('dobs.operation_status') }}</th>
                        <th class="col-notes">{{ __('dobs.operation_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $op)
                        @php
                            $notes = trim((string) ($op->notes ?? ''));
                            $salesOrder = trim((string) ($op->related_sales_order_number ?? ''));
                        @endphp
                        <tr>
                            <td class="col-serial">{{ $op->operation_number }}</td>
                            <td class="col-date">{{ $op->operation_date?->format('Y-m-d') ?? $dash }}</td>
                            <td class="col-client">{{ $op->client?->name ?? $dash }}</td>
                            <td class="col-sales-order">{{ $salesOrder !== '' ? $salesOrder : $dash }}</td>
                            <td class="col-item">{{ $op->item?->name ?? $dash }}</td>
                            <td class="col-qty">{{ $op->quantity ?? $dash }}</td>
                            <td class="col-kind">{{ $op->operationKind?->name ?? $dash }}</td>
                            <td class="col-stencil">{{ $op->stencil?->label() ?? $dash }}</td>
                            <td class="col-unit">{{ $op->silk_unit?->label() ?? $dash }}</td>
                            <td class="col-statement">{{ $op->statement ?? $dash }}</td>
                            <td class="col-press">{{ $op->printingSupplier?->name ?? $dash }}</td>
                            <td class="col-status">{{ $op->operationStatus?->name ?? $dash }}</td>
                            <td class="col-notes">{{ $notes !== '' ? $notes : $dash }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="empty-state">
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
                        <th>{{ __('dobs.report_col_kind') }}</th>
                        <th>{{ __('dobs.report_col_total_quantity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->operation_kind_name }}</td>
                            <td>{{ number_format($row->total_quantity) }}</td>
                        </tr>
                    @endforeach
                </tbody>
         
            </table>
        @endif

        <div class="zanka-report-footer">
            <span>{{ __('dobs.report_footer_copyright') }}</span>
            <span>{{ now()->format('n/j/y, g:i A') }}</span>
            <span>{{ __('dobs.app_name') }}</span>
        </div>
    </div>
@endif
@endsection
