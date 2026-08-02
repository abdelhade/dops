@extends('layouts.app')

@section('title', __('dobs.nav_operation_movements'))

@section('header_title', __('dobs.operation_movements_title'))
@section('header_subtitle', __('dobs.operation_movements_subtitle'))

@section('header_actions')
    @if (auth()->user()?->hasPermission('operation-movements', 'create'))
        <a href="{{ route('operation-movements.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_operation_movement') }}
        </a>
    @endif
@endsection

@section('content')
@php
    $operationFilterKeys = ['operation_number', 'client_name', 'operation_status_id', 'type', 'date_from', 'date_to'];
    $hasActiveFilters = collect($operationFilterKeys)->contains(fn ($key) => request()->filled($key));
@endphp

<div class="operations-filters-card glass-card" style="margin-bottom: 1.5rem;">
    <button
        type="button"
        class="btn btn-secondary btn-sm operations-filters-toggle"
        id="operations-filters-toggle"
        aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
        aria-controls="operations-filters-panel"
        style="margin-bottom: {{ $hasActiveFilters ? '1rem' : '0' }};"
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
        <form method="GET" action="{{ route('operation-movements.index') }}" class="filters-form">
            <div class="filters-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group mb-0">
                    <label class="form-label text-muted small">{{ __('dobs.operation_serial') }}</label>
                    <input type="text" name="operation_number" class="form-control form-control-sm" value="{{ request('operation_number') }}">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-muted small">{{ __('dobs.operation_client') }}</label>
                    <input type="text" name="client_name" class="form-control form-control-sm" value="{{ request('client_name') }}">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-muted small">{{ __('dobs.col_status') }}</label>
                    <select name="operation_status_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.all') ?? 'الكل' }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected(request('operation_status_id') == $status->id)>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-muted small">{{ __('dobs.col_movement_type') }}</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.all') ?? 'الكل' }}</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-muted small">{{ __('dobs.date_from') ?? 'من تاريخ' }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-muted small">{{ __('dobs.date_to') ?? 'إلى تاريخ' }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
            </div>
            
            <div class="filters-actions" style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-magnifying-glass"></i> {{ __('dobs.search') ?? 'بحث' }}
                </button>
                <a href="{{ route('operation-movements.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-xmark"></i> {{ __('dobs.clear') ?? 'مسح' }}
                </a>
            </div>
        </form>
    </div>
</div>
<div class="glass-card printable-area">
    @if ($movements->isNotEmpty())
        <ul class="operation-timeline" style="margin-top: 1rem; padding-right: 1rem;">
            @foreach ($movements as $movement)
                <li class="operation-timeline-item">
                    <div class="operation-timeline-marker" aria-hidden="true" style="background-color: {{ $movement->operationStatus?->color ?? 'var(--text-muted)' }}; border-color: {{ $movement->operationStatus?->color ?? 'var(--text-muted)' }};"></div>
                    <div class="operation-timeline-body">
                        <div class="operation-timeline-meta">
                            <time datetime="{{ $movement->datetime ? $movement->datetime->toIso8601String() : '' }}">
                                {{ $movement->datetime ? $movement->datetime->format('Y-m-d h:i A') : __('dobs.dash') }}
                            </time>
                            
                            <a href="{{ route('operations.show', $movement->operation->id) }}" class="operation-timeline-operation" style="color: var(--color-primary); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-file-lines"></i>
                                {{ $movement->operation->operation_number }} - {{ $movement->operation->client?->name ?? __('dobs.dash') }}
                            </a>
                        </div>
                        
                        <div class="operation-timeline-action" style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="badge" style="background-color: {{ $movement->operationStatus?->color ?? 'var(--text-muted)' }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 13px;">
                                    {{ $movement->operationStatus?->name ?? __('dobs.na') }}
                                </span>
                                
                                @php
                                    $typeLabel = match ($movement->type) {
                                        'entry' => __('dobs.type_entry'),
                                        'start' => __('dobs.type_start'),
                                        'end' => __('dobs.type_end'),
                                        'exit' => __('dobs.type_exit'),
                                        default => $movement->type,
                                    };
                                    $badgeColor = match ($movement->type) {
                                        'entry' => 'var(--color-info)',
                                        'start' => 'var(--color-warning)',
                                        'end' => 'var(--color-success)',
                                        'exit' => 'var(--color-danger)',
                                        default => 'var(--text-muted)',
                                    };
                                @endphp
                                <span class="badge" style="background-color: {{ $badgeColor }}; color: white; padding: 4px 8px; border-radius: 4px; font-weight: 500; font-size: 12px;">
                                    {{ $typeLabel }}
                                </span>
                            </div>
                            
                            <div class="operation-timeline-actions">
                                @include('partials.crud-actions', [
                                    'resource' => 'operation-movements',
                                    'editRoute' => route('operation-movements.edit', $movement->id),
                                    'destroyRoute' => route('operation-movements.destroy', $movement->id),
                                    'confirmMessage' => __('dobs.confirm_delete_operation_movement'),
                                ])
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="empty-state" style="text-align: center; padding: 3rem;">
            <i class="fa-solid fa-truck-ramp-box" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <p>{{ __('dobs.no_operation_movements') }}</p>
        </div>
    @endif
    
    @if ($movements->hasPages())
        <div style="margin-top: 20px;">
            {{ $movements->links() }}
        </div>
    @endif
</div>
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
        toggle.style.marginBottom = open ? '1rem' : '0';
    });

    if (toggle.getAttribute('aria-expanded') === 'true') {
        toggle.classList.add('is-open');
        toggle.style.marginBottom = '1rem';
    }
})();
</script>
@endsection
