@extends('layouts.app')

@section('title', __('dobs.dashboard_title'))

@section('header_title', __('dobs.dashboard_title'))
@section('header_subtitle', __('dobs.dashboard_subtitle'))

@section('content')
<div class="stats-grid">
    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_total_earning') }}</span>
            <span class="stat-value">{{ number_format($stats['total_sales'], 2) }} {{ __('dobs.currency') }}</span>
        </div>
        <div class="stat-icon success">
            <i class="fa-solid fa-coins"></i>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_operations') }}</span>
            <span class="stat-value">{{ $stats['operations_count'] }}</span>
        </div>
        <div class="stat-icon primary">
            <i class="fa-solid fa-arrows-spin"></i>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_total_items') }}</span>
            <span class="stat-value">{{ $stats['items_count'] }}</span>
        </div>
        <div class="stat-icon secondary">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('dobs.stat_suppliers') }}</span>
            <span class="stat-value">{{ $stats['suppliers_count'] }}</span>
        </div>
        <div class="stat-icon warning">
            <i class="fa-solid fa-truck-field"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
    <div class="glass-card">
        <div class="card-header-flex">
            <h2 class="card-title">{{ __('dobs.recent_operations') }}</h2>
            <a href="{{ route('operations.index') }}" class="btn btn-secondary btn-sm">{{ __('dobs.view_all') }}</a>
        </div>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __('dobs.col_op_number') }}</th>
                        <th>{{ __('dobs.col_date') }}</th>
                        <th>{{ __('dobs.col_item') }}</th>
                        <th>{{ __('dobs.col_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_operations as $op)
                        <tr>
                            <td>
                                <a href="{{ route('operations.show', $op->id) }}" style="color: var(--color-secondary); font-weight:600; text-decoration:none;">
                                    {{ $op->operation_number }}
                                </a>
                            </td>
                            <td>{{ $op->operation_date?->format('Y-m-d') ?? $op->operation_date }}</td>
                            <td>{{ $op->item?->name ?? __('dobs.dash') }}</td>
                            <td>
                                <span class="badge badge-{{ strtolower($op->status) }}">{{ __('dobs.status_' . strtolower($op->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fa-solid fa-receipt"></i>
                                {{ __('dobs.no_recent_operations') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card" style="border-color: rgba(239, 68, 68, 0.25);">
        <div class="card-header-flex">
            <h2 class="card-title" style="color: #fca5a5;">{{ __('dobs.low_stock_warning') }}</h2>
            <span class="badge badge-danger">{{ __('dobs.alert') }}</span>
        </div>
        <div>
            @forelse($low_stock_items as $item)
                <div class="list-item-flex">
                    <div class="item-details">
                        <a href="{{ route('items.show', $item->id) }}" style="color: var(--text-primary); text-decoration:none;" class="item-title">
                            {{ $item->name }}
                        </a>
                        <span class="item-subtitle">{{ __('dobs.col_sku') }}: {{ $item->sku }}</span>
                    </div>
                    <span style="font-weight: 700; color: var(--color-danger);">{{ __('dobs.left_stock', ['count' => $item->stock]) }}</span>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-circle-check" style="color: var(--color-success);"></i>
                    {{ __('dobs.healthy_stock') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

<div style="margin-top: 1.5rem;">
    <div class="glass-card">
        <div class="card-header-flex">
            <h2 class="card-title">{{ __('dobs.newly_registered_items') }}</h2>
            <a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">{{ __('dobs.manage_items') }}</a>
        </div>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('items.show', $item->id) }}" style="color: whit; font-weight:600; text-decoration:none;">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td><code style="color: var(--color-secondary);">{{ $item->sku }}</code></td>
                            <td>{{ $item->category->name ?? __('dobs.na') }}</td>
                            <td>{{ $item->supplier->name ?? __('dobs.na') }}</td>
                            <td>{{ $item->paperSize->name ?? __('dobs.na') }}</td>
                            <td style="font-weight: 700; color: white;">{{ number_format($item->price, 2) }} {{ __('dobs.currency') }}</td>
                            <td style="color: {{ $item->stock < 50 ? 'var(--color-danger)' : 'var(--text-secondary)' }}">{{ $item->stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fa-solid fa-box"></i>
                                {{ __('dobs.no_items_in_system') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
