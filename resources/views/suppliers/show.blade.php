@extends('layouts.app')

@section('title', __('dobs.supplier_details'))

@section('header_title', $supplier->name)
@section('header_subtitle', __('dobs.supplier_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;">
    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back') }}
    </a>
    @if (auth()->user()?->canEditRecords())
        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> {{ __('dobs.edit') }}
        </a>
    @endif
</div>
@endsection

@section('content')
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom: 1rem;">{{ __('dobs.contact_info') }}</h2>
        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <div>
                <span class="stat-label">{{ __('dobs.supplier_name') }}</span>
                <div style="font-size:1.15rem; font-weight:600; margin-top:0.25rem;">{{ $supplier->name }}</div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.email') }}</span>
                <div style="font-size:0.95rem; color:var(--text-secondary); margin-top:0.25rem;">
                    {{ $supplier->email ?: __('dobs.na') }}
                </div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.phone') }}</span>
                <div style="font-size:0.95rem; color:var(--text-secondary); margin-top:0.25rem;">
                    {{ $supplier->phone ?: __('dobs.na') }}
                </div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.address') }}</span>
                <div style="font-size:0.95rem; color:var(--text-secondary); margin-top:0.25rem; white-space:pre-line;">
                    {{ $supplier->address ?: __('dobs.na') }}
                </div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.total_supplied_items') }}</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--color-secondary); margin-top:0.25rem;">
                    {{ $supplier->items->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom:1.5rem;">{{ __('dobs.supplied_items') }}</h2>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __('dobs.col_item') }}</th>
                        <th>{{ __('dobs.col_sku') }}</th>
                        <th>{{ __('dobs.col_category') }}</th>
                        <th>{{ __('dobs.col_paper_size') }}</th>
                        <th>{{ __('dobs.col_price') }}</th>
                        <th>{{ __('dobs.col_stock') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplier->items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('items.show', $item->id) }}" style="color: whit; font-weight: 600; text-decoration: none;">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td><code style="color: var(--color-secondary);">{{ $item->sku }}</code></td>
                            <td>{{ $item->category->name ?? __('dobs.na') }}</td>
                            <td>{{ $item->paperSize->name ?? __('dobs.na') }}</td>
                            <td style="font-weight: 700; color: white;">{{ number_format($item->price, 2) }} {{ __('dobs.currency') }}</td>
                            <td>{{ $item->stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fa-solid fa-box"></i>
                                {{ __('dobs.no_items_for_supplier') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
