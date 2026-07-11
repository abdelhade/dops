@extends('layouts.app')

@section('title', __('dobs.nav_clients'))

@section('header_title', __('dobs.clients_title'))
@section('header_subtitle', __('dobs.clients_subtitle'))

@section('header_actions')
    @if (auth()->user()?->hasPermission('clients', 'create'))
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

@php
    $canBulkDelete = (bool) auth()->user()?->hasPermission('clients', 'delete');
    $clientFilterKeys = ['search', 'phone', 'email'];
    $hasActiveFilters = collect($clientFilterKeys)->contains(fn ($key) => request()->filled($key));
    $clearFiltersUrl = route('clients.index');
@endphp

<div class="operations-filters-card glass-card no-print" style="margin-bottom: 1rem;">
    <button
        type="button"
        class="btn btn-secondary btn-sm operations-filters-toggle"
        id="clients-filters-toggle"
        aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
        aria-controls="clients-filters-panel"
    >
        <i class="fa-solid fa-filter"></i>
        <span>{{ __('dobs.filters') }}</span>
        @if ($hasActiveFilters)
            <span class="operations-filters-badge" aria-hidden="true"></span>
        @endif
        <i class="fa-solid fa-chevron-down operations-filters-chevron"></i>
    </button>

    <div
        id="clients-filters-panel"
        class="operations-filters-panel"
        @unless($hasActiveFilters) hidden @endunless
    >
        <form method="GET" action="{{ route('clients.index') }}" class="filters-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.report_global_search') }}</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('dobs.report_global_search_placeholder') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.phone') }}</label>
                    <input type="text" name="phone" class="form-control form-control-sm" value="{{ request('phone') }}" placeholder="{{ __('dobs.phone') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.email') }}</label>
                    <input type="text" name="email" class="form-control form-control-sm" value="{{ request('email') }}" placeholder="{{ __('dobs.email') }}">
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-filter"></i> {{ __('dobs.report_show_results') }}
                    </button>
                    <a href="{{ $clearFiltersUrl }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.clear_filters') }}">
                        <i class="fa-solid fa-rotate-left"></i> {{ __('dobs.clear_filters') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

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
                @include('clients._rows')
            </tbody>
        </table>
    </div>
    @if ($clients->hasPages())
        <div id="clients-lazy-sentinel" class="clients-lazy-load-sentinel text-center py-4" style="color: var(--text-muted); font-size: 0.9rem;" data-next-page="{{ $clients->nextPageUrl() }}">
            <i class="fa-solid fa-spinner fa-spin"></i> {{ __('dobs.report_kanban_loading') ?? 'Loading...' }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
    @if (auth()->user()?->hasPermission('clients', 'delete'))
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
    <script>
    (function () {
        const toggle = document.getElementById('clients-filters-toggle');
        const panel = document.getElementById('clients-filters-panel');
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

    document.addEventListener('DOMContentLoaded', function () {
        const sentinel = document.getElementById('clients-lazy-sentinel');
        const container = document.getElementById('clientsBulkTableBody');
        
        if (sentinel && container && window.IntersectionObserver) {
            let isLoading = false;
            let nextPageUrl = sentinel.dataset.nextPage;
            
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !isLoading && nextPageUrl) {
                    isLoading = true;
                    
                    fetch(nextPageUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        container.insertAdjacentHTML('beforeend', data.html);
                        
                        if (data.has_more && data.next_page_url) {
                            nextPageUrl = data.next_page_url;
                            sentinel.dataset.nextPage = nextPageUrl;
                        } else {
                            nextPageUrl = null;
                            sentinel.remove();
                            observer.disconnect();
                        }
                        isLoading = false;
                    })
                    .catch(error => {
                        console.error('Error loading more clients:', error);
                        isLoading = false;
                    });
                }
            }, {
                rootMargin: '300px',
                threshold: 0
            });
            
            observer.observe(sentinel);
        }
    });
    </script>
@endsection
