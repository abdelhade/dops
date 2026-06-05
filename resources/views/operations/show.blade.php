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
@endphp

<div class="glass-card" style="max-width: 900px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem 2rem;">
        <div>
            <span class="stat-label">{{ __('dobs.operation_serial') }}</span>
            <div style="font-family: monospace; font-weight: 700; color: var(--color-secondary); margin-top: 0.25rem;">{{ $operation->operation_number }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.operation_status') }}</span>
            <div style="margin-top: 0.35rem;">
                <span class="badge" style="background-color: {{ $operation->operationStatus?->color ?? '#6c757d' }}; color: white;">
                    {{ $operation->operationStatus?->name ?? __('dobs.dash') }}
                </span>
            </div>
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
            <span class="stat-label">{{ __('dobs.operation_product_1') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->item?->name ?? $dash }}</div>
        </div>
        <div>
            <span class="stat-label">{{ __('dobs.col_quantity') }}</span>
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $field($operation->quantity) }}</div>
        </div>
        <div style="grid-column: 1 / -1;">
            <span class="stat-label">{{ __('dobs.operation_statement') }}</span>
            <div style="margin-top: 0.25rem; white-space: pre-line; color: var(--text-secondary);">{{ $field($operation->statement ?? $operation->notes) }}</div>
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
            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $operation->job_size !== null ? number_format($operation->job_size, 2) : $dash }}</div>
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

<div class="glass-card operation-timeline-card no-print" style="max-width: 900px; margin: 1.5rem auto 0;">
    <h3 class="card-title" style="margin-bottom: 1.25rem;">
        <i class="fa-solid fa-clock-rotate-left"></i> {{ __('dobs.operation_history') }}
    </h3>

    @include('partials.operation-log-timeline', ['logs' => $operation->logs])
</div>

<style>
    @media print {
        body { background: white !important; color: black !important; }
        .sidebar, .no-print, .page-subtitle { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .glass-card { background: transparent !important; border: none !important; box-shadow: none !important; }
        .stat-label, div { color: black !important; }
        .badge { border: 1px solid black !important; color: black !important; background: transparent !important; }
    }
</style>
@endsection
