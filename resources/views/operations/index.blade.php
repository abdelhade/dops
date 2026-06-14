@extends('layouts.app')

@section('title', __('dobs.nav_operations'))

@section('header_title', __('dobs.operations_title'))
@section('header_subtitle', __('dobs.operations_subtitle'))

@section('header_actions')
    <div class="operations-header-actions">
        <form method="GET" action="{{ route('operations.index') }}" class="operations-type-form" id="operations-type-form">
            <label for="operations-index-type" class="operations-type-form-label">{{ __('dobs.operation_type') }}</label>
            <select name="operation_type" id="operations-index-type" class="form-control operations-type-select" onchange="this.form.submit()">
                @foreach($operationTypes as $typeOption)
                    <option value="{{ $typeOption->slug }}" @selected($operationType->slug === $typeOption->slug)>
                        {{ $typeOption->name }}
                    </option>
                @endforeach
            </select>
        </form>

        @if (auth()->user()?->canCreateRecords())
            <a href="{{ route('operations.create', ['operation_type' => $operationType->slug]) }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> {{ __('dobs.new_operation') }}
            </a>
        @endif
    </div>
@endsection

@section('content')
@php
    $isGeneralIndex = $operationType->isGeneral();
    $operationFilterKeys = [
        'operation_number', 'date_from', 'date_to', 'item_id', 'operation_status_id',
        'printing_supplier_id', 'paper_type_id', 'color_count', 'statement',
    ];

    if ($isGeneralIndex) {
        $operationFilterKeys[] = 'operation_kind_id';
        $operationFilterKeys[] = 'stencil';
        $operationFilterKeys[] = 'silk_unit';
    } else {
        $operationFilterKeys[] = 'ctp_supplier_id';
        $operationFilterKeys[] = 'service_id';
    }

    $hasActiveFilters = collect($operationFilterKeys)->contains(fn ($key) => request()->filled($key));
    $clearFiltersUrl = route('operations.index', ['operation_type' => $operationType->slug]);
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
        @include('operations._filters', [
            'operationType' => $operationType,
            'clearFiltersUrl' => $clearFiltersUrl,
        ])
    </div>
</div>

@if ($operations->isEmpty())
    <div class="glass-card operations-empty-card">
        <div class="empty-state">
            <i class="fa-solid fa-receipt"></i>
            {{ __('dobs.no_operations') }}
        </div>
    </div>
@else
    <div class="operations-cards-grid" id="operations-cards-container">
        @include('operations._cards')
    </div>

    @if ($operations->hasPages())
        <div id="operations-lazy-sentinel" class="operations-lazy-load-sentinel text-center py-4" style="color: var(--text-muted); font-size: 0.9rem;" data-next-page="{{ $operations->nextPageUrl() }}">
            <i class="fa-solid fa-spinner fa-spin"></i> {{ __('dobs.report_kanban_loading') ?? 'Loading...' }}
        </div>
    @endif
@endif
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

document.addEventListener('DOMContentLoaded', function () {
    const sentinel = document.getElementById('operations-lazy-sentinel');
    const container = document.getElementById('operations-cards-container');
    
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
                    console.error('Error loading more operations:', error);
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
