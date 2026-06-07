@extends('layouts.app')

@section('title', $operation->operation_number)

@section('header_title', $operation->operation_number)
@section('header_subtitle', __('dobs.operation_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;" class="no-print">
    <a href="{{ route('operations.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
    </a>

    <a href="{{ route('operations.export', $operation) }}" class="btn btn-secondary">
        <i class="fa-solid fa-file-excel"></i> {{ __('dobs.download_excel') }}
    </a>

    <button type="button" onclick="window.print();" class="btn btn-secondary">
        <i class="fa-solid fa-print"></i> {{ __('dobs.print_operation') }}
    </button>

    @php
        $isCompleted = false;
        if ($operation->operationStatus) {
            $isCompleted = in_array(strtolower($operation->operationStatus->name), ['completed', 'مكتمل', 'منتهي']);
        }
    @endphp
    @if (!$isCompleted && auth()->user()?->canEditRecords())
        <a href="{{ route('operations.edit', $operation->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> {{ __('dobs.edit_operation') }}
        </a>
    @endif
</div>
@endsection

@section('content')
@php
    $dash = __('dobs.dash');
    $field = fn ($value) => filled($value) ? $value : $dash;
    $jobSizeLabel = $operation->job_size !== null
        ? number_format((float) $operation->job_size, 0)
        : ($operation->reportPaperDimension() ?? '');
    $notes = trim((string) ($operation->notes ?? ''));
@endphp

<div class="glass-card operation-show-screen no-print" style="max-width: 900px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem 2rem;">
        <div>
            <span class="stat-label">{{ __('dobs.operation_serial') }}</span>
            <div style="font-family: monospace; font-weight: 700; color: var(--color-secondary); margin-top: 0.25rem;">{{ $operation->operation_number }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_date') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->operation_date?->format('Y-m-d') ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_current_time') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->formattedOperationTime() ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_client') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->client?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_product_1') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->item?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.col_quantity') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->quantity) }}</div>
        </div>
        <div style="grid-column: 1 / -1;">
            <span class="stat-label">{{ __('dobs.operation_statement') }}</span>
            <div style="margin-top: 0.25rem; white-space: pre-line; color: var(--text-secondary);">{{ $field($operation->statement) }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_printing_press') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->printingSupplier?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_ctp') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->ctpSupplier?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_color_count') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->color_count) }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_paper_material') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->paperType?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_job_size') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $jobSizeLabel !== '' ? $jobSizeLabel : $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_pull_count') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->pull_count) }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_quantity_per_sheet') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->quantity_per_sheet) }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_service_1') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->service1?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_service_2') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->service2?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_service_3') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->service3?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_status') }}</span>
            <div style="margin-top: 0.35rem;">
                <span class="badge" style="background-color: {{ $operation->operationStatus?->color ?? '#6c757d' }}; color: white;">
                    {{ $operation->operationStatus?->name ?? $dash }}
                </span>
            </div>
        </div>
        <div style="grid-column: 1 / -1;">
            <span class="stat-label">{{ __('dobs.operation_notes') }}</span>
            <div style="margin-top: 0.25rem; white-space: pre-line; color: var(--text-secondary);">{{ $notes !== '' ? $notes : $dash }}</div>
        </div>
    </div>

    @if(!$operation->item_id && $operation->items->isNotEmpty())
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08);">
            <h3 class="card-title" style="margin-bottom: 1rem;">{{ __('dobs.legacy_operation_items') }}</h3>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __('dobs.col_item') }}</th>
                            <th>{{ __('dobs.col_quantity') }}</th>
                            <th>{{ __('dobs.col_unit_price') }}</th>
                            <th>{{ __('dobs.col_notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operation->items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->pivot->quantity }}</td>
                                <td>{{ number_format($item->pivot->unit_price, 2) }} {{ __('dobs.currency') }}</td>
                                <td>{{ $item->pivot->notes ?: $dash }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($isCompleted)
        <p style="font-size:0.8rem; color:var(--text-muted); margin-top:1.5rem;">
            <i class="fa-solid fa-lock"></i> {{ __('dobs.locked_completed') }}
        </p>
    @endif
</div>

<div class="operation-print-sheet print-only">
    <div class="operation-print-header">
        <h2 class="operation-print-title">{{ __('dobs.dobs_print_system') }}</h2>
        <div class="operation-print-meta">
            {{ $operation->operation_number }}
            @if ($operation->operation_date)
                — {{ $operation->operation_date->format('Y-m-d') }}
            @endif
        </div>
    </div>

    <table class="zanka-report-table">
        <thead>
            <tr>
                <th class="col-serial">{{ __('dobs.operation_serial') }}</th>
                <th class="col-date">{{ __('dobs.col_date') }}</th>
                <th class="col-client">{{ __('dobs.operation_client') }}</th>
                <th class="col-item">{{ __('dobs.log_field_item_id') }}</th>
                <th class="col-qty">{{ __('dobs.col_quantity') }}</th>
                <th class="col-statement">{{ __('dobs.operation_statement') }}</th>
                <th class="col-press">{{ __('dobs.operation_printing_press') }}</th>
                <th class="col-ctp">{{ __('dobs.operation_ctp') }}</th>
                <th class="col-colors">{{ __('dobs.operation_color_count') }}</th>
                <th class="col-paper">{{ __('dobs.operation_paper_material') }}</th>
                <th class="col-size">{{ __('dobs.operation_job_size') }}</th>
                <th class="col-pull">{{ __('dobs.operation_pull_count') }}</th>
                <th class="col-qty-sheet">{{ __('dobs.operation_quantity_per_sheet') }}</th>
                <th class="col-service">{{ __('dobs.operation_service_1') }}</th>
                <th class="col-service">{{ __('dobs.operation_service_2') }}</th>
                <th class="col-service">{{ __('dobs.operation_service_3') }}</th>
                <th class="col-status">{{ __('dobs.operation_status') }}</th>
                <th class="col-notes">{{ __('dobs.operation_notes') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-serial">{{ $operation->operation_number }}</td>
                <td class="col-date">{{ $operation->operation_date?->format('Y-m-d') ?? $dash }}</td>
                <td class="col-client">{{ $operation->client?->name ?? $dash }}</td>
                <td class="col-item">{{ $operation->item?->name ?? $dash }}</td>
                <td class="col-qty">{{ $operation->quantity ?? $dash }}</td>
                <td class="col-statement">{{ $operation->statement ?? $dash }}</td>
                <td class="col-press">{{ $operation->printingSupplier?->name ?? $dash }}</td>
                <td class="col-ctp">{{ $operation->ctpSupplier?->name ?? $dash }}</td>
                <td class="col-colors">{{ $operation->color_count ?? $dash }}</td>
                <td class="col-paper">{{ $operation->paperType?->name ?? $dash }}</td>
                <td class="col-size">{{ $jobSizeLabel !== '' ? $jobSizeLabel : $dash }}</td>
                <td class="col-pull">{{ $operation->pull_count ?? $dash }}</td>
                <td class="col-qty-sheet">{{ $operation->quantity_per_sheet ?? $dash }}</td>
                <td class="col-service">{{ $operation->service1?->name ?? $dash }}</td>
                <td class="col-service">{{ $operation->service2?->name ?? $dash }}</td>
                <td class="col-service">{{ $operation->service3?->name ?? $dash }}</td>
                <td class="col-status">{{ $operation->operationStatus?->name ?? $dash }}</td>
                <td class="col-notes">{{ $notes !== '' ? $notes : $dash }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="glass-card operation-timeline-card no-print" style="max-width: 900px; margin: 1.5rem auto 0;">
    <h3 class="card-title" style="margin-bottom: 1.25rem;">
        <i class="fa-solid fa-clock-rotate-left"></i> {{ __('dobs.operation_history') }}
    </h3>

    @include('partials.operation-log-timeline', ['logs' => $operation->logs])
</div>

<style>
    .print-only {
        display: none;
    }

    .operation-print-header {
        margin-bottom: 0.75rem;
        text-align: center;
    }

    .operation-print-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #000;
    }

    .operation-print-meta {
        margin-top: 0.25rem;
        font-size: 0.85rem;
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
    .zanka-report-table .col-client { width: 5rem; text-align: right; }
    .zanka-report-table .col-item { width: 5rem; text-align: right; }
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
    .zanka-report-table tbody td.col-item,
    .zanka-report-table tbody td.col-statement,
    .zanka-report-table tbody td.col-paper,
    .zanka-report-table tbody td.col-service,
    .zanka-report-table tbody td.col-notes {
        text-align: right;
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

        .sidebar,
        .no-print,
        .page-subtitle,
        .top-header,
        .alert {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .page-content-body {
            padding: 0 !important;
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
    }
</style>
@endsection
