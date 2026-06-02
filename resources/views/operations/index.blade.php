@extends('layouts.app')

@section('title', __('dobs.nav_operations'))

@section('header_title', __('dobs.operations_title'))
@section('header_subtitle', __('dobs.operations_subtitle'))

@section('header_actions')
<a href="{{ route('operations.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> {{ __('dobs.new_operation') }}
</a>
@endsection

@section('content')
<div class="glass-card">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>{{ __('dobs.col_op_number') }}</th>
                    <th>{{ __('dobs.col_date') }}</th>
                    <th>{{ __('dobs.col_status') }}</th>
                    <th>{{ __('dobs.col_total_value') }}</th>
                    <th>{{ __('dobs.col_items_selected') }}</th>
                    <th>{{ __('dobs.col_notes_header') }}</th>
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
                        <td>{{ $op->operation_date }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower($op->status) }}">{{ __('dobs.status_' . strtolower($op->status)) }}</span>
                        </td>
                        <td style="font-weight: 700; color: white;">{{ number_format($op->total_amount, 2) }} {{ __('dobs.currency') }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ __('dobs.items_count', ['count' => $op->items_count]) }}</span>
                        </td>
                        <td style="color: var(--text-secondary); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $op->notes ?: __('dobs.no_notes') }}
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('operations.show', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.invoice_view') }}">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </a>
                                @if ($op->status !== 'Completed')
                                    <a href="{{ route('operations.edit', $op->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endif
                                <form action="{{ route('operations.destroy', $op->id) }}" method="POST" onsubmit="return confirm(@json(__('dobs.confirm_delete_operation')));" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
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
@endsection
