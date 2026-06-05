@extends('layouts.app')

@section('title', __('dobs.nav_operations'))

@section('header_title', __('dobs.operations_title'))
@section('header_subtitle', __('dobs.operations_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('operations.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_operation') }}
        </a>
    @endif
@endsection

@section('content')
@php
    $operationFilterKeys = [
        'operation_number', 'date_from', 'date_to', 'item_id', 'operation_status_id',
        'printing_supplier_id', 'ctp_supplier_id', 'paper_type_id', 'color_count', 'service_id', 'statement',
    ];
    $hasActiveFilters = collect($operationFilterKeys)->contains(fn ($key) => request()->filled($key));
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
        @include('operations._filters')
    </div>
</div>

<div class="glass-card">
    <div class="table-container" style="overflow-x: auto; white-space: nowrap;">
        <table class="custom-table" style="min-width: 1800px;">
            <thead>
                <tr>
                    <th>{{ __('dobs.col_op_number') }}</th>
                    <th>{{ __('dobs.col_date') }}</th>
                    <th>{{ __('dobs.col_time') }}</th>
                    <th>{{ __('dobs.col_item') }}</th>
                    <th>{{ __('dobs.col_quantity') }}</th>
                    <th>{{ __('dobs.operation_printing_press') }}</th>
                    <th>{{ __('dobs.operation_ctp') }}</th>
                    <th>{{ __('dobs.operation_color_count') }}</th>
                    <th>{{ __('dobs.operation_paper_material') }}</th>
                    <th>{{ __('dobs.operation_job_size') }}</th>
                    <th>{{ __('dobs.operation_pull_count') }}</th>
                    <th>{{ __('dobs.operation_quantity_per_sheet') }}</th>
                    <th>{{ __('dobs.operation_service_1') }}</th>
                    <th>{{ __('dobs.operation_service_2') }}</th>
                    <th>{{ __('dobs.operation_service_3') }}</th>
                    <th>{{ __('dobs.col_status') }}</th>
                    <th>{{ __('dobs.operation_statement') }}</th>
                    <th style="text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operations as $op)
                    <tr>
                        <td>
                            <a href="{{ route('operations.show', $op->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $op->operation_number }}
                            </a>
                        </td>
                        <td>{{ $op->operation_date?->format('Y-m-d') ?? $op->operation_date }}</td>
                        <td>{{ $op->formattedOperationTime() ?? __('dobs.dash') }}</td>
                        <td>{{ $op->item?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->quantity ?? __('dobs.dash') }}</td>
                        <td>{{ $op->printingSupplier?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->ctpSupplier?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->color_count ?? __('dobs.dash') }}</td>
                        <td>{{ $op->paperType?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->job_size ?? __('dobs.dash') }}</td>
                        <td>{{ $op->pull_count ?? __('dobs.dash') }}</td>
                        <td>{{ $op->quantity_per_sheet ?? __('dobs.dash') }}</td>
                        <td>{{ $op->service1?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->service2?->name ?? __('dobs.dash') }}</td>
                        <td>{{ $op->service3?->name ?? __('dobs.dash') }}</td>
                        <td>
                            @php
                                $isCompleted = false;
                                if ($op->operationStatus) {
                                    $isCompleted = in_array(strtolower($op->operationStatus->name), ['completed', 'مكتمل', 'منتهي']);
                                }
                            @endphp
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
                                        @foreach(App\Models\OperationStatus::orderBy('sort_order')->get() as $statusOpt)
                                            <option value="{{ $statusOpt->id }}" @selected($op->operation_status_id == $statusOpt->id)>
                                                {{ $statusOpt->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="badge" style="background-color: {{ $op->operationStatus?->color ?? '#6c757d' }}; color: white;">
                                    {{ $op->operationStatus?->name ?? __('dobs.dash') }}
                                </span>
                            @endif
                        </td>
                        <td style="color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $op->statement ?? $op->notes ?? __('dobs.no_notes') }}
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('operations.show', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.view_details') }}">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if (!$isCompleted && auth()->user()?->canEditRecords())
                                    <a href="{{ route('operations.edit', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endif
                                @if (auth()->user()?->canCreateRecords())
                                    <a href="{{ route('operations.create', ['copy_from' => $op->id]) }}" class="btn btn-secondary btn-sm" title="نسخ العملية">
                                        <i class="fa-solid fa-copy"></i>
                                    </a>
                                @endif
                                @if (auth()->user()?->canDeleteRecords())
                                    <form action="{{ route('operations.destroy', $op->id) }}" method="POST" onsubmit="return confirm(@json(__('dobs.confirm_delete_operation')));" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="18" class="empty-state">
                            <i class="fa-solid fa-receipt"></i>
                            {{ __('dobs.no_operations') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($operations->hasPages())
        <div style="padding: 1rem; border-top: 1px solid var(--border-color);">
            {{ $operations->links() }}
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
    });

    if (toggle.getAttribute('aria-expanded') === 'true') {
        toggle.classList.add('is-open');
    }
})();
</script>
@endsection
