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
<div class="glass-card printable-area">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 5%">{{ __('dobs.col_id') }}</th>
                    <th style="width: 25%">{{ __('dobs.col_operation') }}</th>
                    <th style="width: 25%">{{ __('dobs.col_service') }}</th>
                    <th style="width: 20%">{{ __('dobs.col_movement_type') }}</th>
                    <th style="width: 15%">{{ __('dobs.col_datetime') }}</th>
                    <th style="width: 10%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('operations.show', $movement->operation->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $movement->operation->operation_number }}
                            </a>
                        </td>
                        <td>
                            @if ($movement->service)
                                <a href="{{ route('services.show', $movement->service->id) }}" style="color: white; text-decoration: none;">
                                    {{ $movement->service->name }}
                                </a>
                            @else
                                <span class="badge badge-secondary" style="background-color: var(--bg-modifier-accent); color: var(--text-muted); padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                    {{ __('dobs.na') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $typeLabel = match ($movement->type) {
                                    'entry' => __('dobs.type_entry'),
                                    'start' => __('dobs.type_start'),
                                    'end' => __('dobs.type_end'),
                                    'exit' => __('dobs.type_exit'),
                                    default => $movement->type,
                                };
                                $badgeColor = match ($movement->type) {
                                    'entry' => '#3b82f6', // blue
                                    'start' => '#f59e0b', // orange
                                    'end' => '#10b981',   // green
                                    'exit' => '#ef4444',  // red
                                    default => '#6b7280',
                                };
                            @endphp
                            <span class="badge" style="background-color: {{ $badgeColor }}; color: white; padding: 4px 8px; border-radius: 4px; font-weight: 500; font-size: 12px;">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td style="color: var(--text-secondary);">
                            {{ $movement->datetime ? $movement->datetime->format('Y-m-d H:i') : __('dobs.dash') }}
                        </td>
                        <td>
                            @include('partials.crud-actions', [
                                'editRoute' => route('operation-movements.edit', $movement->id),
                                'destroyRoute' => route('operation-movements.destroy', $movement->id),
                                'confirmMessage' => __('dobs.confirm_delete_operation_movement'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            {{ __('dobs.no_operation_movements') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if ($movements->hasPages())
        <div style="margin-top: 20px;">
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection
