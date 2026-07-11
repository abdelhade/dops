@extends('layouts.app')

@section('title', __('dobs.nav_services'))

@section('header_title', __('dobs.services_title'))
@section('header_subtitle', __('dobs.services_subtitle'))

@section('header_actions')
    @if (auth()->user()?->hasPermission('services', 'create'))
        <a href="{{ route('services.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_service') }}
        </a>
    @endif
@endsection

@section('styles')
    @include('partials.print-styles')
@endsection

@section('content')
@include('partials.list-export-print', ['exportRoute' => route('services.export')])
<div class="glass-card printable-area">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 5%">{{ __('dobs.col_id') }}</th>
                    <th style="width: 40%">{{ __('dobs.service_name') }}</th>
                    <th style="width: 30%">{{ __('dobs.description') }}</th>
                    <th style="width: 25; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('services.show', $service->id) }}" style="color: whit; font-weight: 600; text-decoration: none;">
                                {{ $service->name }}
                            </a>
                        </td>
                        <td style="color: var(--text-secondary);">{{ Str::limit($service->description, 50) ?: __('dobs.dash') }}</td>
                        <td>
                            @include('partials.crud-actions', [
                'resource' => 'services',
                                'showRoute' => route('services.show', $service->id),
                                'editRoute' => route('services.edit', $service->id),
                                'destroyRoute' => route('services.destroy', $service->id),
                                'confirmMessage' => __('dobs.confirm_delete_service'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fa-solid fa-bell-concierge"></i>
                            {{ __('dobs.no_services') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
