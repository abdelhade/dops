@extends('layouts.app')

@section('title', $operation->operation_number)

@section('header_title', $operation->operation_number)
@section('header_subtitle', __('dobs.operation_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;" class="no-print">
    <a href="{{ route('operations.index', ['operation_type' => $operation->operation_type?->value ?? 'offset']) }}" class="btn btn-secondary">
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
    $isSilkScreen = $operation->isSilkScreen();
    $isOffset = $operation->isOffset();
@endphp

<div class="glass-card operation-show-screen no-print" style="max-width: 900px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem 2rem;">
        <div>
            <span class="stat-label">{{ __('dobs.operation_serial') }}</span>
            <div style="font-family: monospace; font-weight: 700; color: var(--color-secondary); margin-top: 0.25rem;">{{ $operation->operation_number }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_type') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->operation_type?->label() ?? $dash }}</div>
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
            <span class="stat-label">{{ __('dobs.operation_related_sales_order_number') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->related_sales_order_number) }}</div>
        </div>
        <div>
            <span class="stat-label">{{ $isSilkScreen ? __('dobs.operation_silk_final_product') : __('dobs.operation_product_1') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->item?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.col_quantity') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->quantity) }}</div>
        </div>
        @if($isSilkScreen)
        <div>
            <span class="stat-label">{{ __('dobs.operation_silk_unit') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->silk_unit?->label() ?? $dash }}</div>
        </div>
        @endif
        <div style="grid-column: 1 / -1;">
            <span class="stat-label">{{ __('dobs.operation_statement') }}</span>
            <div style="margin-top: 0.25rem; white-space: pre-line; color: var(--text-secondary);">{{ $field($operation->statement) }}</div>
        </div>
        <div>
            <span class="stat-label">{{ $isSilkScreen ? __('dobs.operation_silk_supplier') : __('dobs.operation_printing_press') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->printingSupplier?->name ?? $dash }}</div>
        </div>
        @if($isOffset)
        <div>
            <span class="stat-label">{{ __('dobs.operation_ctp') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->ctpSupplier?->name ?? $dash }}</div>
        </div>
        @endif
        <div>
            <span class="stat-label">{{ __('dobs.operation_color_count') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->color_count) }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_paper_material') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->paperType?->name ?? $dash }}</div>
        </div>
        @if($isSilkScreen)
        <div>
            <span class="stat-label">{{ __('dobs.operation_silk_print_preparations') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->stencil?->label() ?? $dash }}</div>
        </div>
        @endif
        @if($isOffset)
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
        @endif
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

<div class="operation-print-card print-only">
    <header class="operation-print-header">
        <div class="operation-print-brand">{{ __('dobs.app_name') }}</div>
        <h1 class="operation-print-title">{{ __('dobs.nav_operations') }} — {{ $operation->operation_number }}</h1>
        <div class="operation-print-meta">
            <span>{{ __('dobs.operation_date') }}: {{ $operation->operation_date?->format('Y-m-d') ?? $dash }}</span>
            @if ($operation->formattedOperationTime())
                <span>{{ __('dobs.operation_current_time') }}: {{ $operation->formattedOperationTime() }}</span>
            @endif
        </div>
    </header>

    <div class="operation-print-body">
        <div class="operation-print-grid">
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_serial') }}</span>
                <span class="operation-print-value operation-print-value-mono">{{ $operation->operation_number }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_type') }}</span>
                <span class="operation-print-value">{{ $operation->operation_type?->label() ?? $dash }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_date') }}</span>
                <span class="operation-print-value">{{ $operation->operation_date?->format('Y-m-d') ?? $dash }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_client') }}</span>
                <span class="operation-print-value">{{ $operation->client?->name ?? $dash }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_related_sales_order_number') }}</span>
                <span class="operation-print-value">{{ $field($operation->related_sales_order_number) }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ $isSilkScreen ? __('dobs.operation_silk_final_product') : __('dobs.operation_product_1') }}</span>
                <span class="operation-print-value">{{ $operation->item?->name ?? $dash }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.col_quantity') }}</span>
                <span class="operation-print-value">{{ $field($operation->quantity) }}</span>
            </div>
            @if($isSilkScreen)
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_silk_unit') }}</span>
                <span class="operation-print-value">{{ $operation->silk_unit?->label() ?? $dash }}</span>
            </div>
            @endif
            <div class="operation-print-field operation-print-field-full">
                <span class="operation-print-label">{{ __('dobs.operation_statement') }}</span>
                <span class="operation-print-value operation-print-value-block">{{ $field($operation->statement) }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ $isSilkScreen ? __('dobs.operation_silk_supplier') : __('dobs.operation_printing_press') }}</span>
                <span class="operation-print-value">{{ $operation->printingSupplier?->name ?? $dash }}</span>
            </div>
            @if($isOffset)
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_ctp') }}</span>
                <span class="operation-print-value">{{ $operation->ctpSupplier?->name ?? $dash }}</span>
            </div>
            @endif
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_color_count') }}</span>
                <span class="operation-print-value">{{ $field($operation->color_count) }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_paper_material') }}</span>
                <span class="operation-print-value">{{ $operation->paperType?->name ?? $dash }}</span>
            </div>
            @if($isSilkScreen)
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_silk_print_preparations') }}</span>
                <span class="operation-print-value">{{ $operation->stencil?->label() ?? $dash }}</span>
            </div>
            @endif
            @if($isOffset)
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_job_size') }}</span>
                <span class="operation-print-value">{{ $jobSizeLabel !== '' ? $jobSizeLabel : $dash }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_pull_count') }}</span>
                <span class="operation-print-value">{{ $field($operation->pull_count) }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_quantity_per_sheet') }}</span>
                <span class="operation-print-value">{{ $field($operation->quantity_per_sheet) }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_service_1') }}</span>
                <span class="operation-print-value">{{ $operation->service1?->name ?? $dash }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_service_2') }}</span>
                <span class="operation-print-value">{{ $operation->service2?->name ?? $dash }}</span>
            </div>
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_service_3') }}</span>
                <span class="operation-print-value">{{ $operation->service3?->name ?? $dash }}</span>
            </div>
            @endif
            <div class="operation-print-field">
                <span class="operation-print-label">{{ __('dobs.operation_status') }}</span>
                <span class="operation-print-value">{{ $operation->operationStatus?->name ?? $dash }}</span>
            </div>
            <div class="operation-print-field operation-print-field-full">
                <span class="operation-print-label">{{ __('dobs.operation_notes') }}</span>
                <span class="operation-print-value operation-print-value-block">{{ $notes !== '' ? $notes : $dash }}</span>
            </div>
        </div>
    </div>

    <footer class="operation-print-footer">
        <span>{{ __('dobs.operation_printed_at', ['datetime' => now()->format('Y-m-d H:i')]) }}</span>
        <span>{{ __('dobs.report_footer_copyright') }}</span>
    </footer>
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

    .operation-print-card {
        max-width: 720px;
        margin: 0 auto;
        border: 2px solid #222;
        background: #fff;
        color: #000;
    }

    .operation-print-header {
        padding: 1rem 1.25rem;
        text-align: center;
        border-bottom: 2px solid #222;
        background: #f5f5f5;
    }

    .operation-print-brand {
        font-size: 1.15rem;
        font-weight: 700;
        color: #000;
    }

    .operation-print-title {
        margin: 0.35rem 0 0;
        font-size: 1rem;
        font-weight: 700;
        color: #000;
    }

    .operation-print-meta {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem 1.25rem;
        margin-top: 0.5rem;
        font-size: 0.82rem;
        color: #333;
    }

    .operation-print-body {
        padding: 1rem 1.25rem;
    }

    .operation-print-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem 1.5rem;
    }

    .operation-print-field {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }

    .operation-print-field-full {
        grid-column: 1 / -1;
    }

    .operation-print-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #555;
        text-transform: none;
    }

    .operation-print-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: #000;
        word-wrap: break-word;
    }

    .operation-print-value-mono {
        font-family: monospace;
    }

    .operation-print-value-block {
        white-space: pre-line;
        font-weight: 500;
        line-height: 1.45;
    }

    .operation-print-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 0.65rem 1.25rem;
        border-top: 2px solid #222;
        background: #f5f5f5;
        font-size: 0.72rem;
        color: #555;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm;
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

        .operation-print-card {
            border: 2px solid #000 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .operation-print-header,
        .operation-print-footer {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endsection
