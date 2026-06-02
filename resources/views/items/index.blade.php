@extends('layouts.app')

@section('title', __('dobs.nav_items'))

@section('header_title', __('dobs.items_title'))
@section('header_subtitle', __('dobs.items_subtitle'))

@section('header_actions')
<a href="{{ route('items.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> {{ __('dobs.new_item') }}
</a>
@endsection

@section('content')
<div class="glass-card">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>{{ __('dobs.col_item') }}</th>
                    <th>{{ __('dobs.col_sku') }}</th>
                    <th>{{ __('dobs.col_category') }}</th>
                    <th>{{ __('dobs.col_supplier') }}</th>
                    <th>{{ __('dobs.col_paper_size') }}</th>
                    <th>{{ __('dobs.col_price') }}</th>
                    <th>{{ __('dobs.col_stock') }}</th>
                    <th style="text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            <a href="{{ route('items.show', $item->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $item->name }}
                            </a>
                        </td>
                        <td><code style="color: var(--color-secondary);">{{ $item->sku }}</code></td>
                        <td>
                            @if ($item->category)
                                <a href="{{ route('categories.show', $item->category_id) }}" style="color: var(--text-secondary); text-decoration:none; border-bottom: 1px dashed rgba(255,255,255,0.2);">
                                    {{ $item->category->name }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">{{ __('dobs.na') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($item->supplier)
                                <a href="{{ route('suppliers.show', $item->supplier_id) }}" style="color: var(--text-secondary); text-decoration:none; border-bottom: 1px dashed rgba(255,255,255,0.2);">
                                    {{ $item->supplier->name }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">{{ __('dobs.na') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($item->paperSize)
                                <a href="{{ route('paper-sizes.show', $item->paper_size_id) }}" style="color: var(--text-secondary); text-decoration:none; border-bottom: 1px dashed rgba(255,255,255,0.2);">
                                    {{ $item->paperSize->name }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">{{ __('dobs.na') }}</span>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: white;">{{ number_format($item->price, 2) }} {{ __('dobs.currency') }}</td>
                        <td>
                            @if ($item->stock < 50)
                                <span style="color: var(--color-danger); font-weight: 600;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $item->stock }}
                                </span>
                            @else
                                <span style="color: var(--text-secondary);">{{ $item->stock }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('items.show', $item->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.view') }}">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('items.edit', $item->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('items.destroy', $item->id) }}" method="POST" onsubmit="return confirm(@json(__('dobs.confirm_delete_item')));" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-box"></i>
                            {{ __('dobs.no_items') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
