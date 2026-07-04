@extends('layouts.app')

@section('title', __('dobs.nav_clients'))

@section('header_title', __('dobs.clients_title'))
@section('header_subtitle', __('dobs.clients_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('clients.daftara.sync-form') }}" class="btn btn-secondary" style="margin-left: 0.5rem; margin-right: 0.5rem;">
            <i class="fa-solid fa-cloud-arrow-down"></i> {{ __('dobs.daftara_sync') }}
        </a>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_client') }}
        </a>
    @endif
@endsection

@section('styles')
    @include('partials.print-styles')
    <style>
        .clients-bulk-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .clients-bulk-count {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .clients-bulk-checkbox {
            width: 1rem;
            height: 1rem;
            cursor: pointer;
            accent-color: var(--color-primary);
        }

        .clients-bulk-flash {
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
@include('partials.list-export-print', ['exportRoute' => route('clients.export')])

@php($canBulkDelete = (bool) auth()->user()?->canDeleteRecords())

<div
    id="clientsBulkRoot"
    class="glass-card printable-area"
    @if ($canBulkDelete)
        data-bulk-url="{{ route('clients.bulk-destroy') }}"
        data-bulk-csrf="{{ csrf_token() }}"
    @endif
>
    @if ($canBulkDelete)
        <div id="clientsBulkBar" class="clients-bulk-bar no-print" hidden>
            <span id="clientsBulkCount" class="clients-bulk-count"></span>
            <button type="button" id="clientsBulkDeleteBtn" class="btn btn-danger btn-sm">
                <i class="fa-solid fa-trash"></i> {{ __('dobs.bulk_delete') }}
            </button>
        </div>
    @endif

    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    @if ($canBulkDelete)
                        <th style="width: 3%; text-align: center;">
                            <input
                                type="checkbox"
                                id="clientsBulkSelectAll"
                                class="clients-bulk-checkbox"
                                title="{{ __('dobs.bulk_select_all') }}"
                                aria-label="{{ __('dobs.bulk_select_all') }}"
                            >
                        </th>
                    @endif
                    <th style="width: 5%">{{ __('dobs.col_id') }}</th>
                    <th style="width: 25%">{{ __('dobs.client_name') }}</th>
                    <th style="width: 20%">{{ __('dobs.email') }}</th>
                    <th style="width: 15%">{{ __('dobs.phone') }}</th>
                    <th style="width: 20%">{{ __('dobs.address') }}</th>
                    <th style="width: 15%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody id="clientsBulkTableBody">
                @forelse($clients as $client)
                    <tr>
                        @if ($canBulkDelete)
                            <td style="text-align: center;">
                                <input
                                    type="checkbox"
                                    class="clients-bulk-checkbox clients-bulk-row-cb"
                                    value="{{ $client->id }}"
                                    aria-label="{{ __('dobs.bulk_select_item', ['name' => $client->name]) }}"
                                >
                            </td>
                        @endif
                        <td>{{ $loop->iteration }}</td>
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
                        <td colspan="{{ $canBulkDelete ? 7 : 6 }}" class="empty-state">
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

@section('scripts')
    @if (auth()->user()?->canDeleteRecords())
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script>
            window.CLIENTS_BULK_LANG = {
                selectedCount: @json(__('dobs.bulk_selected_count')),
                confirmDelete: @json(__('dobs.confirm_bulk_delete_clients')),
                noSelection: @json(__('dobs.bulk_no_selection')),
                deleteFailed: @json(__('dobs.bulk_delete_failed')),
                noClients: @json(__('dobs.no_clients')),
            };
        </script>
        <script src="{{ asset('js/clients-bulk-actions.js') }}?v={{ @filemtime(public_path('js/clients-bulk-actions.js')) ?: 1 }}"></script>
    @endif
@endsection
