@extends('layouts.app')

@section('title', __('dobs.category_details'))

@section('header_title', $category->name)
@section('header_subtitle', __('dobs.category_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;">
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back') }}
    </a>
    @if (auth()->user()?->canEditRecords())
        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> {{ __('dobs.edit') }}
        </a>
    @endif
</div>
@endsection

@section('content')
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom: 1rem;">{{ __('dobs.category_info') }}</h2>
        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <div>
                <span class="stat-label">{{ __('dobs.col_name') }}</span>
                <div style="font-size:1.15rem; font-weight:600; margin-top:0.25rem;">{{ $category->name }}</div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.description') }}</span>
                <div style="font-size:0.95rem; color:var(--text-secondary); margin-top:0.25rem; white-space:pre-line;">
                    {{ $category->description ?: __('dobs.no_description_provided') }}
                </div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.total_assigned_items') }}</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--color-secondary); margin-top:0.25rem;">
                    {{ $category->items->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom:1.5rem;">{{ __('dobs.assigned_items') }}</h2>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __('dobs.col_item') }}</th>
                        <th>{{ __('dobs.col_sku') }}</th>
                        <th>{{ __('dobs.col_supplier') }}</th>
                        <th>{{ __('dobs.col_paper_size') }}</th>
                        <th>{{ __('dobs.col_price') }}</th>
                        <th>{{ __('dobs.col_stock') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($category->items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('items.show', $item->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td><code style="color: var(--color-secondary);">{{ $item->sku }}</code></td>
                            <td>{{ $item->supplier->name ?? __('dobs.na') }}</td>
                            <td>{{ $item->paperSize->name ?? __('dobs.na') }}</td>
                            <td style="font-weight: 700; color: white;">{{ number_format($item->price, 2) }} {{ __('dobs.currency') }}</td>
                            <td>{{ $item->stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fa-solid fa-box"></i>
                                {{ __('dobs.no_items_in_category') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
