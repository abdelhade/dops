@extends('layouts.app')

@section('title', __('dobs.nav_clients'))

@section('header_title', __('dobs.clients_title'))
@section('header_subtitle', __('dobs.clients_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_client') }}
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
                    <th style="width: 25%">{{ __('dobs.client_name') }}</th>
                    <th style="width: 20%">{{ __('dobs.email') }}</th>
                    <th style="width: 15%">{{ __('dobs.phone') }}</th>
                    <th style="width: 20%">{{ __('dobs.address') }}</th>
                    <th style="width: 15; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>
                            <a href="{{ route('clients.show', $client->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $client->name }}
                            </a>
                        </td>
                        <td>{{ $client->email ?? __('dobs.na') }}</td>
                        <td>{{ $client->phone ?? __('dobs.na') }}</td>
                        <td style="color: var(--text-secondary);">{{ $client->address ?? __('dobs.na') }}</td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('clients.show', $client->id),
                                'editRoute' => route('clients.edit', $client->id),
                                'destroyRoute' => route('clients.destroy', $client->id),
                                'confirmMessage' => __('dobs.confirm_delete_client'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fa-solid fa-user-tie"></i>
                            {{ __('dobs.no_clients') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
