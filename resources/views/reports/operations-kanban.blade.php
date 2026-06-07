@extends('layouts.app')

@section('title', __('dobs.report_operations_kanban'))

@section('header_title', __('dobs.report_operations_kanban'))
@section('header_subtitle', __('dobs.report_operations_kanban_subtitle'))

@section('styles')
<style>
    .ops-kanban-filters {
        margin-bottom: 1.25rem;
        padding: 1.1rem 1.25rem;
        border: 1px solid var(--border-color);
        background: linear-gradient(180deg, rgba(31, 41, 55, 0.55) 0%, rgba(17, 24, 39, 0.75) 100%);
    }

    .ops-kanban-filters-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        align-items: flex-end;
    }

    .ops-kanban-filter-field {
        margin-bottom: 0;
        min-width: 160px;
    }

    .ops-kanban-filter-search {
        flex: 1;
        min-width: 220px;
    }

    .ops-kanban-filter-search-wrap {
        position: relative;
    }

    .ops-kanban-filter-search-wrap i {
        position: absolute;
        top: 50%;
        right: 0.85rem;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .ops-kanban-filter-search-wrap .form-control {
        padding-right: 2.35rem;
    }

    .ops-kanban-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .ops-kanban-hint {
        margin: 0;
        font-size: 0.82rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .ops-kanban-board-wrap {
        height: calc(100vh - 15rem);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0.25rem 0.15rem 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        background: rgba(17, 24, 39, 0.35);
    }

    .ops-kanban-board {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-content: start;
        padding: 0.5rem;
    }

    .ops-kanban-column {
        display: flex;
        flex-direction: column;
        min-height: 0;
        background: rgba(17, 24, 39, 0.55);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .ops-kanban-column-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.85rem 1rem;
        border-top: 3px solid var(--color-secondary);
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid var(--border-color);
    }

    .ops-kanban-column-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
    }

    .ops-kanban-column-count {
        flex-shrink: 0;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: rgba(139, 92, 246, 0.15);
        color: var(--color-primary);
        border: 1px solid rgba(139, 92, 246, 0.25);
    }

    .ops-kanban-column-body {
        height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        scrollbar-gutter: stable;
    }

    .ops-kanban-column-body.is-drag-over {
        background: rgba(6, 182, 212, 0.06);
    }

    .ops-kanban-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.75rem;
        cursor: grab;
        transition: box-shadow 0.15s ease, transform 0.15s ease;
    }

    .ops-kanban-card:hover {
        border-color: rgba(6, 182, 212, 0.35);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    }

    .ops-kanban-card.is-dragging {
        opacity: 0.55;
        cursor: grabbing;
    }

    .ops-kanban-card.is-readonly {
        cursor: default;
    }

    .ops-kanban-card-serial {
        display: block;
        font-family: ui-monospace, monospace;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--color-secondary);
        text-decoration: none;
        margin-bottom: 0.65rem;
        line-height: 1.3;
    }

    .ops-kanban-card-serial:hover {
        color: var(--text-primary);
        text-decoration: underline;
    }

    .ops-kanban-card-datetime {
        font-size: 0.74rem;
        color: var(--text-muted);
        margin-bottom: 0.45rem;
        line-height: 1.35;
    }

    .ops-kanban-card-product {
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.35rem;
        line-height: 1.4;
        word-break: break-word;
    }

    .ops-kanban-card-client {
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--text-secondary);
        line-height: 1.4;
        word-break: break-word;
    }

    .ops-kanban-empty,
    .ops-kanban-loading,
    .ops-kanban-sentinel {
        text-align: center;
        padding: 0.85rem 0.5rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .ops-kanban-sentinel {
        min-height: 1px;
        padding: 0.25rem;
    }

    .ops-kanban-toast {
        position: fixed;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1200;
        padding: 0.65rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        display: none;
    }

    .ops-kanban-toast.is-success {
        background: rgba(16, 185, 129, 0.95);
        color: #fff;
    }

    .ops-kanban-toast.is-error {
        background: rgba(239, 68, 68, 0.95);
        color: #fff;
    }

    @media (max-width: 768px) {
        .ops-kanban-board {
            grid-template-columns: 1fr;
        }

        .ops-kanban-board-wrap {
            height: calc(100vh - 17rem);
        }

        .ops-kanban-column-body {
            height: 260px;
        }
    }
</style>
@endsection

@section('content')
<div class="glass-card ops-kanban-filters no-print">
    <form id="opsKanbanFiltersForm" class="ops-kanban-filters-row">
        <div class="form-group ops-kanban-filter-field ops-kanban-filter-search">
            <label for="opsKanbanSearch" class="form-label">{{ __('dobs.report_global_search') }}</label>
            <div class="ops-kanban-filter-search-wrap">
                <input
                    type="search"
                    id="opsKanbanSearch"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="{{ __('dobs.report_kanban_search_placeholder') }}"
                    autocomplete="off"
                >
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            </div>
        </div>
        <div class="form-group ops-kanban-filter-field">
            <label for="opsKanbanDateFrom" class="form-label">{{ __('dobs.date_from') }}</label>
            <input
                type="date"
                id="opsKanbanDateFrom"
                name="date_from"
                class="form-control"
                value="{{ request('date_from') }}"
            >
        </div>
        <div class="form-group ops-kanban-filter-field">
            <label for="opsKanbanDateTo" class="form-label">{{ __('dobs.date_to') }}</label>
            <input
                type="date"
                id="opsKanbanDateTo"
                name="date_to"
                class="form-control"
                value="{{ request('date_to') }}"
            >
        </div>
        <div class="form-group ops-kanban-filter-field" style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> {{ __('dobs.apply_filters') }}
            </button>
            <button type="button" class="btn btn-secondary" id="opsKanbanClearFilters">
                <i class="fa-solid fa-eraser"></i> {{ __('dobs.clear_filters') }}
            </button>
        </div>
    </form>
</div>

<div class="ops-kanban-toolbar no-print">
    <p class="ops-kanban-hint">
        <i class="fa-solid fa-hand-pointer" aria-hidden="true"></i>
        {{ __('dobs.report_kanban_drag_hint') }}
    </p>
    <button type="button" class="btn btn-secondary btn-sm" id="opsKanbanRefreshBtn">
        <i class="fa-solid fa-rotate"></i> {{ __('dobs.report_kanban_refresh') }}
    </button>
</div>

<div class="ops-kanban-board-wrap no-print">
    <div class="ops-kanban-board" id="opsKanbanBoard">
        @foreach ($statuses as $status)
            <div
                class="ops-kanban-column"
                data-ops-kanban-column
                data-status-id="{{ $status->id }}"
                data-status-color="{{ $status->color ?? '#06b6d4' }}"
            >
                <div
                    class="ops-kanban-column-header"
                    style="border-top-color: {{ $status->color ?? '#06b6d4' }};"
                >
                    <span class="ops-kanban-column-title">{{ $status->name }}</span>
                    <span class="ops-kanban-column-count" data-ops-kanban-count>0</span>
                </div>
                <div class="ops-kanban-column-body" data-ops-kanban-list></div>
            </div>
        @endforeach
    </div>
</div>

<div id="opsKanbanToast" class="ops-kanban-toast" role="status" aria-live="polite"></div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    window.OPS_KANBAN_CONFIG = {
        loadUrl: @json(route('reports.operations-kanban.load')),
        statusUpdateUrlTemplate: @json(route('operations.update-status', ['operation' => '__ID__'])),
        csrfToken: @json(csrf_token()),
        canDrag: @json((bool) auth()->user()?->canEditRecords()),
        dash: @json(__('dobs.dash')),
    };
    window.OPS_KANBAN_LANG = {
        loading: @json(__('dobs.report_kanban_loading')),
        loadMore: @json(__('dobs.report_kanban_load_more')),
        emptyColumn: @json(__('dobs.report_kanban_empty_column')),
        statusUpdateFailed: @json(__('dobs.report_kanban_status_update_failed')),
        operationsCount: @json(__('dobs.report_kanban_operations_count')),
    };
</script>
<script src="{{ asset('js/operations-kanban.js') }}?v={{ @filemtime(public_path('js/operations-kanban.js')) ?: 1 }}"></script>
@endsection
