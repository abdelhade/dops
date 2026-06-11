@extends('layouts.app')

@section('title', __('dobs.nav_operations'))

@section('header_title', __('dobs.operations_title'))
@section('header_subtitle', __('dobs.operations_subtitle'))

@section('header_actions')
    <div class="operations-header-actions">
        <form method="GET" action="{{ route('operations.index') }}" class="operations-type-form" id="operations-type-form">
            <label for="operations-index-type" class="operations-type-form-label">{{ __('dobs.operation_type') }}</label>
            <select name="operation_type" id="operations-index-type" class="form-control operations-type-select" onchange="this.form.submit()">
                @foreach(\App\Enums\OperationType::casesForSelect() as $typeOption)
                    <option value="{{ $typeOption->value }}" @selected($operationType->value === $typeOption->value)>
                        {{ $typeOption->label() }}
                    </option>
                @endforeach
            </select>
        </form>

        @if (auth()->user()?->canCreateRecords())
            <a href="{{ route('operations.create', ['operation_type' => $operationType->value]) }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> {{ __('dobs.new_operation') }}
            </a>
        @endif
    </div>
@endsection

@section('content')
@php
    use App\Enums\OperationType;

    $isSilkScreenIndex = $operationType === OperationType::SilkScreen;
    $operationFilterKeys = [
        'operation_number', 'date_from', 'date_to', 'item_id', 'operation_status_id',
        'printing_supplier_id', 'paper_type_id', 'color_count', 'statement',
    ];

    if ($isSilkScreenIndex) {
        $operationFilterKeys[] = 'stencil';
        $operationFilterKeys[] = 'silk_unit';
    } else {
        $operationFilterKeys[] = 'ctp_supplier_id';
        $operationFilterKeys[] = 'service_id';
    }

    $hasActiveFilters = collect($operationFilterKeys)->contains(fn ($key) => request()->filled($key));
    $clearFiltersUrl = route('operations.index', ['operation_type' => $operationType->value]);
@endphp

<div class="operations-filters-card glass-card">
    <button
        type="button"
        class="btn btn-secondary btn-sm operations-filters-toggle"
        id="operations-filters-toggle"
        aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
        aria-controls="operations-filters-panel"
    >
        <i class="fa-solid fa-filter"></i>
        <span>{{ __('dobs.filters') }}</span>
        @if ($hasActiveFilters)
            <span class="operations-filters-badge" aria-hidden="true"></span>
        @endif
        <i class="fa-solid fa-chevron-down operations-filters-chevron"></i>
    </button>

    <div
        id="operations-filters-panel"
        class="operations-filters-panel"
        @unless($hasActiveFilters) hidden @endunless
    >
        @include('operations._filters', [
            'operationType' => $operationType,
            'clearFiltersUrl' => $clearFiltersUrl,
        ])
    </div>
</div>

@if ($operations->isEmpty())
    <div class="glass-card operations-empty-card">
        <div class="empty-state">
            <i class="fa-solid fa-receipt"></i>
            {{ __('dobs.no_operations') }}
        </div>
    </div>
@else
    <div class="operations-cards-grid">
        @foreach ($operations as $op)
            @php
                $isCompleted = false;
                if ($op->operationStatus) {
                    $isCompleted = in_array(strtolower($op->operationStatus->name), ['completed', 'مكتمل', 'منتهي']);
                }
                $services = collect([$op->service1, $op->service2, $op->service3])->filter();
                $statement = $op->statement ?? $op->notes;
                $isOpSilkScreen = $op->isSilkScreen();
            @endphp

            <article class="operation-card glass-card">
                <header class="operation-card-header">
                    <div class="operation-card-title-block">
                        <a href="{{ route('operations.show', $op->id) }}" class="operation-card-number">
                            {{ $op->operation_number }}
                        </a>
                        <div class="operation-card-datetime">
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            <span>{{ $op->operation_date?->format('Y-m-d') ?? __('dobs.dash') }}</span>
                            <span class="operation-card-datetime-sep">·</span>
                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                            <span>{{ $op->formattedOperationTime() ?? __('dobs.dash') }}</span>
                        </div>
                    </div>

                    <div class="operation-card-status">
                        @if (auth()->user()?->canEditRecords())
                            <form
                                action="{{ route('operations.update-status', $op) }}"
                                method="POST"
                                class="operation-status-form"
                            >
                                @csrf
                                @method('PATCH')
                                <select
                                    name="operation_status_id"
                                    class="form-control form-control-sm operation-status-select"
                                    style="background-color: {{ $op->operationStatus?->color ?? '#333' }}; color: white;"
                                    aria-label="{{ __('dobs.operation_status') }}"
                                    onchange="this.form.submit()"
                                >
                                    @foreach ($operationStatuses as $statusOpt)
                                        <option value="{{ $statusOpt->id }}" @selected($op->operation_status_id == $statusOpt->id)>
                                            {{ $statusOpt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="badge operation-card-status-badge" style="background-color: {{ $op->operationStatus?->color ?? '#6c757d' }}; color: white;">
                                {{ $op->operationStatus?->name ?? __('dobs.dash') }}
                            </span>
                        @endif
                    </div>
                </header>

                <div class="operation-card-item">
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                    <span>{{ $op->item?->name ?? __('dobs.dash') }}</span>
                </div>

                <div class="operation-card-metrics">
                    <div class="operation-card-metric">
                        <span class="operation-card-metric-label">{{ __('dobs.col_quantity') }}</span>
                        <span class="operation-card-metric-value">{{ $op->quantity ?? __('dobs.dash') }}</span>
                    </div>
                    <div class="operation-card-metric">
                        <span class="operation-card-metric-label">{{ __('dobs.operation_color_count') }}</span>
                        <span class="operation-card-metric-value">{{ $op->color_count ?? __('dobs.dash') }}</span>
                    </div>
                    @if($isOpSilkScreen)
                    <div class="operation-card-metric">
                        <span class="operation-card-metric-label">{{ __('dobs.operation_stencil') }}</span>
                        <span class="operation-card-metric-value">{{ $op->stencil?->label() ?? __('dobs.dash') }}</span>
                    </div>
                    <div class="operation-card-metric">
                        <span class="operation-card-metric-label">{{ __('dobs.operation_silk_unit') }}</span>
                        <span class="operation-card-metric-value">{{ $op->silk_unit?->label() ?? __('dobs.dash') }}</span>
                    </div>
                    @else
                    <div class="operation-card-metric">
                        <span class="operation-card-metric-label">{{ __('dobs.operation_pull_count') }}</span>
                        <span class="operation-card-metric-value">{{ $op->pull_count ?? __('dobs.dash') }}</span>
                    </div>
                    <div class="operation-card-metric">
                        <span class="operation-card-metric-label">{{ __('dobs.operation_quantity_per_sheet') }}</span>
                        <span class="operation-card-metric-value">{{ $op->quantity_per_sheet ?? __('dobs.dash') }}</span>
                    </div>
                    @endif
                </div>

                <div class="operation-card-details">
                    @if ($op->paperType)
                        <span class="operation-card-chip operation-card-chip--paper">
                            <i class="fa-solid fa-scroll" aria-hidden="true"></i>
                            {{ $op->paperType->name }}
                        </span>
                    @endif
                    @if ($op->printingSupplier)
                        <span class="operation-card-chip">
                            <i class="fa-solid fa-print" aria-hidden="true"></i>
                            {{ $op->printingSupplier->name }}
                        </span>
                    @endif
                    @if (!$isOpSilkScreen && $op->ctpSupplier)
                        <span class="operation-card-chip">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                            {{ $op->ctpSupplier->name }}
                        </span>
                    @endif
                    @if (!$isOpSilkScreen && filled($op->job_size))
                        <span class="operation-card-chip">
                            {{ __('dobs.operation_job_size') }}: {{ $op->job_size }}
                        </span>
                    @endif
                    @if (!$isOpSilkScreen)
                        @foreach ($services as $service)
                            <span class="operation-card-chip operation-card-chip--service">
                                <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                                {{ $service->name }}
                            </span>
                        @endforeach
                    @endif
                </div>

                @if (filled($statement))
                    <p class="operation-card-statement">{{ $statement }}</p>
                @endif

                <footer class="operation-card-footer">
                    <a href="{{ route('operations.show', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.view_details') }}">
                        <i class="fa-solid fa-eye"></i>
                        {{ __('dobs.view') }}
                    </a>
                    @if (!$isCompleted && auth()->user()?->canEditRecords())
                        <a href="{{ route('operations.edit', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    @endif
                    @if (auth()->user()?->canCreateRecords())
                        <a href="{{ route('operations.create', ['copy_from' => $op->id, 'operation_type' => $operationType->value]) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.copy_operation') }}">
                            <i class="fa-solid fa-copy"></i>
                        </a>
                    @endif
                    @if (auth()->user()?->canDeleteRecords())
                        <form
                            action="{{ route('operations.destroy', $op->id) }}"
                            method="POST"
                            class="operation-card-delete-form dobs-delete-form"
                            data-dobs-delete
                            data-dobs-confirm="{{ __('dobs.confirm_delete_operation') }}"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </footer>
            </article>
        @endforeach
    </div>

    @if ($operations->hasPages())
        <div class="operations-cards-pagination">
            {{ $operations->links() }}
        </div>
    @endif
@endif
@endsection

@section('scripts')
<script>
(function () {
    const toggle = document.getElementById('operations-filters-toggle');
    const panel = document.getElementById('operations-filters-panel');
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
