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
<div class="glass-card">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>{{ __('dobs.col_op_number') }}</th>
                    <th>{{ __('dobs.col_date') }}</th>
                    <th>{{ __('dobs.col_time') }}</th>
                    <th>{{ __('dobs.col_item') }}</th>
                    <th>{{ __('dobs.col_quantity') }}</th>
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
                        <td>
                            <span class="badge badge-{{ strtolower($op->status) }}">{{ __('dobs.status_' . strtolower($op->status)) }}</span>
                        </td>
                        <td style="color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $op->statement ?? $op->notes ?? __('dobs.no_notes') }}
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('operations.show', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.view_details') }}">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if ($op->status !== 'Completed' && auth()->user()?->canEditRecords())
                                    <a href="{{ route('operations.edit', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
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
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-receipt"></i>
                            {{ __('dobs.no_operations') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
