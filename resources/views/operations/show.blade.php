@extends('layouts.app')

@section('title', $operation->operation_number)

@section('header_title', $operation->operation_number)
@section('header_subtitle', __('dobs.operation_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;" class="no-print">
    <a href="{{ route('operations.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
    </a>

    <button type="button" onclick="window.print();" class="btn btn-secondary">
        <i class="fa-solid fa-print"></i> {{ __('dobs.print_invoice') }}
    </button>

    @if ($operation->status !== 'Completed')
        <a href="{{ route('operations.edit', $operation->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> {{ __('dobs.edit_operation') }}
        </a>
    @endif
</div>
@endsection

@section('content')
<div class="invoice-container">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <div class="glass-card">
            <h2 class="card-title" style="margin-bottom:1.5rem;">{{ __('dobs.included_items') }}</h2>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __('dobs.col_item_details') }}</th>
                            <th>{{ __('dobs.col_category') }}</th>
                            <th>{{ __('dobs.col_paper_size') }}</th>
                            <th>{{ __('dobs.col_unit_price') }}</th>
                            <th>{{ __('dobs.col_quantity') }}</th>
                            <th>{{ __('dobs.col_notes') }}</th>
                            <th style="text-align: left;">{{ __('dobs.col_subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operation->items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: white;">{{ $item->name }}</div>
                                    <code style="font-size: 0.75rem; color: var(--color-secondary);">{{ $item->sku }}</code>
                                </td>
                                <td>{{ $item->category->name ?? __('dobs.na') }}</td>
                                <td>{{ $item->paperSize->name ?? __('dobs.na') }}</td>
                                <td>{{ number_format($item->pivot->unit_price, 2) }} {{ __('dobs.currency') }}</td>
                                <td>{{ $item->pivot->quantity }}</td>
                                <td style="color:var(--text-secondary); font-size:0.85rem;">{{ $item->pivot->notes ?: __('dobs.dash') }}</td>
                                <td style="font-weight: 700; color: white; text-align: left;">
                                    {{ number_format($item->pivot->quantity * $item->pivot->unit_price, 2) }} {{ __('dobs.currency') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.5rem; margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--border-color);">
                <div style="display:flex; justify-content:space-between; width:250px; font-size:0.9rem; color:var(--text-secondary);">
                    <span>{{ __('dobs.subtotal_label') }}</span>
                    <span>{{ number_format($operation->total_amount, 2) }} {{ __('dobs.currency') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; width:250px; font-size:0.9rem; color:var(--text-secondary);">
                    <span>{{ __('dobs.tax_zero') }}</span>
                    <span>0.00 {{ __('dobs.currency') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; width:250px; font-size:1.35rem; font-weight:800; color:var(--color-success); border-top:1px solid rgba(255,255,255,0.1); padding-top:0.5rem;">
                    <span>{{ __('dobs.grand_total') }}</span>
                    <span>{{ number_format($operation->total_amount, 2) }} {{ __('dobs.currency') }}</span>
                </div>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="glass-card">
                <h3 class="card-title" style="margin-bottom:1.25rem;">{{ __('dobs.operation_info') }}</h3>

                <div style="display:flex; flex-direction:column; gap:1.25rem;">
                    <div>
                        <span class="stat-label">{{ __('dobs.operation_ref') }}</span>
                        <div style="font-size:1.15rem; font-weight:700; font-family:monospace; color:var(--color-secondary); margin-top:0.25rem;">
                            {{ $operation->operation_number }}
                        </div>
                    </div>

                    <div>
                        <span class="stat-label">{{ __('dobs.col_status') }}</span>
                        <div style="margin-top:0.35rem;">
                            <span class="badge badge-{{ strtolower($operation->status) }}" style="font-size: 0.85rem; padding: 0.35rem 0.85rem;">
                                {{ __('dobs.status_' . strtolower($operation->status)) }}
                            </span>
                        </div>
                        @if ($operation->status === 'Completed')
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.5rem;">
                                <i class="fa-solid fa-lock"></i> {{ __('dobs.locked_completed') }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <span class="stat-label">{{ __('dobs.operation_date') }}</span>
                        <div style="font-size:0.95rem; font-weight:600; margin-top:0.25rem;">
                            {{ $operation->operation_date }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card" style="flex:1;">
                <h3 class="card-title" style="margin-bottom:1rem;">{{ __('dobs.work_notes') }}</h3>
                <div style="font-size:0.9rem; color:var(--text-secondary); white-space:pre-line;">
                    {{ $operation->notes ?: __('dobs.no_work_notes_recorded') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body {
            background: white !important;
            color: black !important;
            font-size: 12pt;
        }
        .sidebar, .no-print, .page-subtitle {
            display: none !important;
        }
        .main-content {
            margin-right: 0 !important;
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .glass-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .custom-table th {
            color: black !important;
            border-bottom: 2px solid black !important;
        }
        .custom-table td {
            color: black !important;
            border-bottom: 1px solid #ddd !important;
        }
        .badge {
            border: 1px solid black !important;
            color: black !important;
            background: transparent !important;
        }
        .page-title {
            color: black !important;
        }
    }
</style>
@endsection
