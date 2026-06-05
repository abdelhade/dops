@extends('layouts.app')

@section('title', __('dobs.report_paper_materials_summary'))

@section('header_title', __('dobs.report_paper_materials_summary'))
@section('header_subtitle', __('dobs.report_paper_materials_summary_subtitle'))

@section('styles')
    @include('partials.print-styles')
@endsection

@section('content')
@php
    $hasActiveFilters = request()->filled('date_from') || request()->filled('date_to');
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

<div class="glass-card printable-area report-section-card">
    <h2 class="report-section-title">
        <i class="fa-solid fa-list" aria-hidden="true"></i>
        {{ __('dobs.report_operations_list') }}
    </h2>
    <div class="table-container" style="overflow-x: auto;">
        <table class="custom-table report-operations-table">
            <thead>
                <tr>
                    <th>{{ __('dobs.col_op_number') }}</th>
                    <th>{{ __('dobs.col_date') }}</th>
                    <th>{{ __('dobs.col_item') }}</th>
                    <th>{{ __('dobs.operation_paper_material') }}</th>
                    <th>{{ __('dobs.operation_pull_count') }}</th>
                    <th>{{ __('dobs.operation_quantity_per_sheet') }}</th>
                    <th>{{ __('dobs.col_status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operations as $op)
                    <tr>
                        <td>
                            <a href="{{ route('operations.show', $op->id) }}" class="report-op-link">
                                {{ $op->operation_number }}
                            </a>
                        </td>
                        <td>{{ $op->operation_date?->format('Y-m-d') ?? __('dobs.dash') }}</td>
                        <td>{{ $op->item?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->paperType?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->pull_count ?? __('dobs.dash') }}</td>
                        <td>{{ $op->quantity_per_sheet ?? __('dobs.dash') }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $op->operationStatus?->color ?? '#6c757d' }}; color: white;">
                                {{ $op->operationStatus?->name ?? __('dobs.dash') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fa-solid fa-receipt"></i>
                            {{ __('dobs.no_operations') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="glass-card printable-area report-section-card">
    <h2 class="report-section-title">
        <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
        {{ __('dobs.report_summary_section') }}
    </h2>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>{{ __('dobs.operation_paper_material') }}</th>
                    <th>{{ __('dobs.operation_pull_count') }}</th>
                    <th>{{ __('dobs.operation_quantity_per_sheet') }}</th>
                    <th>{{ __('dobs.report_operations_count') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td style="font-weight: 600;">{{ $row->paper_type_name }}</td>
                        <td>{{ number_format($row->total_pull_count) }}</td>
                        <td>{{ number_format($row->total_quantity_per_sheet) }}</td>
                        <td>{{ number_format($row->operations_count) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fa-solid fa-chart-column"></i>
                            {{ __('dobs.report_no_data') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr style="font-weight: 700; border-top: 2px solid var(--border-color);">
                        <td>{{ __('dobs.report_totals') }}</td>
                        <td>{{ number_format($totals->total_pull_count) }}</td>
                        <td>{{ number_format($totals->total_quantity_per_sheet) }}</td>
                        <td>{{ number_format($totals->operations_count) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
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
