@extends('layouts.app')

@section('title', __('dobs.nav_operation_types'))

@section('header_title', __('dobs.operation_types_title'))
@section('header_subtitle', __('dobs.operation_types_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('operation-types.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_operation_type') }}
        </a>
    @endif
@endsection

@section('content')
<div class="glass-card">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 5%">{{ __('dobs.col_id') }}</th>
                    <th style="width: 20%">{{ __('dobs.operation_type_name') }}</th>
                    <th style="width: 15%">{{ __('dobs.operation_type_slug') }}</th>
                    <th style="width: 15%">{{ __('dobs.operation_type_form_mode') }}</th>
                    <th style="width: 10%">{{ __('dobs.operation_type_serial_prefix') }}</th>
                    <th style="width: 10%">{{ __('dobs.sort_order') }}</th>
                    <th style="width: 10%">{{ __('dobs.operation_type_system') }}</th>
                    <th style="width: 15%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operationTypes as $type)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td style="font-weight: 600;">{{ $type->name }}</td>
                        <td><code>{{ $type->slug }}</code></td>
                        <td>{{ $type->form_mode?->label() ?? __('dobs.dash') }}</td>
                        <td><code>{{ $type->serial_prefix }}</code></td>
                        <td>{{ $type->sort_order }}</td>
                        <td>
                            @if ($type->is_system)
                                <span class="badge badge-success">{{ __('dobs.yes') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('dobs.no') }}</span>
                            @endif
                        </td>
                        <td>
                            @if (auth()->user()?->canEditRecords())
                                <a href="{{ route('operation-types.edit', $type) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            @endif
                            @if (!$type->is_system && auth()->user()?->canDeleteRecords())
                                <form
                                    action="{{ route('operation-types.destroy', $type) }}"
                                    method="POST"
                                    class="d-inline dobs-delete-form"
                                    data-dobs-delete
                                    data-dobs-confirm="{{ __('dobs.confirm_delete_operation_type') }}"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-layer-group"></i>
                            {{ __('dobs.no_operation_types') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
