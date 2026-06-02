@extends('layouts.app')

@section('title', $item->name)

@section('header_title', $item->name)
@section('header_subtitle', __('dobs.item_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;">
    <a href="{{ route('items.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back') }}
    </a>
    @if (auth()->user()?->canEditRecords())
        <a href="{{ route('items.edit', $item->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> {{ __('dobs.edit') }}
        </a>
    @endif
</div>
@endsection

@section('content')
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom: 1.5rem;">{{ __('dobs.specifications') }}</h2>

        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <div>
                <span class="stat-label">{{ __('dobs.sku') }}</span>
                <div style="font-size:1.1rem; color:var(--color-secondary); font-weight:700; font-family:monospace; margin-top:0.25rem;">
                    {{ $item->sku }}
                </div>
            </div>

            <div>
                <span class="stat-label">{{ __('dobs.stock_status') }}</span>
                <div style="margin-top:0.25rem;">
                    @if($item->stock < 50)
                        <span style="font-size:1.35rem; font-weight:700; color:var(--color-danger);">
                            {{ $item->stock }} <span style="font-size:0.85rem; font-weight:normal;">{{ __('dobs.low_stock_alert') }}</span>
                        </span>
                    @else
                        <span style="font-size:1.35rem; font-weight:700; color:var(--color-success);">
                            {{ $item->stock }} <span style="font-size:0.85rem; font-weight:normal; color:var(--text-secondary);">{{ __('dobs.in_stock') }}</span>
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <span class="stat-label">{{ __('dobs.price_per_unit') }}</span>
                <div style="font-size:1.35rem; font-weight:700; color:white; margin-top:0.25rem;">
                    {{ number_format($item->price, 2) }} {{ __('dobs.currency') }}
                </div>
            </div>

            <div>
                <span class="stat-label">{{ __('dobs.col_category') }}</span>
                <div style="font-size:1rem; font-weight:600; margin-top:0.25rem;">
                    @if($item->category)
                        <a href="{{ route('categories.show', $item->category_id) }}" style="color:var(--text-primary); text-decoration:none;">
                            {{ $item->category->name }}
                        </a>
                    @else
                        <span style="color:var(--text-muted);">{{ __('dobs.none') }}</span>
                    @endif
                </div>
            </div>

            <div>
                <span class="stat-label">{{ __('dobs.col_supplier') }}</span>
                <div style="font-size:1rem; font-weight:600; margin-top:0.25rem;">
                    @if($item->supplier)
                        <a href="{{ route('suppliers.show', $item->supplier_id) }}" style="color:var(--text-primary); text-decoration:none;">
                            {{ $item->supplier->name }}
                        </a>
                    @else
                        <span style="color:var(--text-muted);">{{ __('dobs.none') }}</span>
                    @endif
                </div>
            </div>

            <div>
                <span class="stat-label">{{ __('dobs.paper_dimensions') }}</span>
                <div style="font-size:1rem; font-weight:600; margin-top:0.25rem;">
                    @if($item->paperSize)
                        <a href="{{ route('paper-sizes.show', $item->paper_size_id) }}" style="color:var(--text-primary); text-decoration:none;">
                            {{ $item->paperSize->name }}
                            @if($item->paperSize->width)
                                <span style="font-size:0.85rem; color:var(--text-secondary); font-weight:normal;">
                                    ({{ __('dobs.paper_size_dimensions', ['width' => number_format($item->paperSize->width), 'height' => number_format($item->paperSize->height), 'unit' => __('dobs.mm_unit')]) }})
                                </span>
                            @endif
                        </a>
                    @else
                        <span style="color:var(--text-muted);">{{ __('dobs.na') }} ({{ __('dobs.non_paper_item') }})</span>
                    @endif
                </div>
            </div>

            <div>
                <span class="stat-label">{{ __('dobs.description') }}</span>
                <div style="font-size:0.9rem; color:var(--text-secondary); margin-top:0.25rem; white-space:pre-line;">
                    {{ $item->description ?: __('dobs.no_detailed_description') }}
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom:1.5rem;">{{ __('dobs.usage_history') }}</h2>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __('dobs.col_op_number') }}</th>
                        <th>{{ __('dobs.col_date') }}</th>
                        <th>{{ __('dobs.col_qty') }}</th>
                        <th>{{ __('dobs.col_unit_price_at_time') }}</th>
                        <th>{{ __('dobs.col_subtotal') }}</th>
                        <th>{{ __('dobs.col_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($item->operations as $op)
                        <tr>
                            <td>
                                <a href="{{ route('operations.show', $op->id) }}" style="color:var(--color-secondary); font-weight:600; text-decoration:none;">
                                    {{ $op->operation_number }}
                                </a>
                            </td>
                            <td>{{ $op->operation_date }}</td>
                            <td>{{ $op->pivot->quantity }}</td>
                            <td>{{ number_format($op->pivot->unit_price, 2) }} {{ __('dobs.currency') }}</td>
                            <td style="font-weight:700; color:white;">
                                {{ number_format($op->pivot->quantity * $op->pivot->unit_price, 2) }} {{ __('dobs.currency') }}
                            </td>
                            <td>
                                <span class="badge badge-{{ strtolower($op->status) }}">{{ __('dobs.status_' . strtolower($op->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fa-solid fa-receipt"></i>
                                {{ __('dobs.no_usage_history') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
