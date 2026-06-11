@extends('layouts.app')

@section('title', __('dobs.nav_operation_kinds'))

@section('header_title', __('dobs.operation_kinds_title'))
@section('header_subtitle', __('dobs.operation_kinds_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('operation-kinds.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_operation_kind') }}
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
                    <th style="width: 30%">{{ __('dobs.operation_kind_name') }}</th>
                    <th style="width: 10%">{{ __('dobs.sort_order') }}</th>
                    <th style="width: 35%">{{ __('dobs.description') }}</th>
                    <th style="width: 20%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operationKinds as $kind)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td style="font-weight: 600;">{{ $kind->name }}</td>
                        <td>{{ $kind->sort_order }}</td>
                        <td style="color: var(--text-secondary);">{{ Str::limit($kind->description, 60) ?: __('dobs.dash') }}</td>
                        <td>
                            @include('partials.crud-actions', [
                                'editRoute' => route('operation-kinds.edit', $kind),
                                'destroyRoute' => route('operation-kinds.destroy', $kind),
                                'confirmMessage' => __('dobs.confirm_delete_operation_kind'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fa-solid fa-tags"></i>
                            {{ __('dobs.no_operation_kinds') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
