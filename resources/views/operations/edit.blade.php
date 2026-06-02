@extends('layouts.app')

@section('title', __('dobs.edit_operation_prefix') . ' ' . $operation->operation_number)

@section('header_title', __('dobs.edit_operation_prefix') . ' ' . $operation->operation_number)
@section('header_subtitle', __('dobs.edit_operation_subtitle'))

@section('header_actions')
<a href="{{ route('operations.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 1000px; margin: 0 auto;">
    <form action="{{ route('operations.update', $operation->id) }}" method="POST" id="operation-form">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label for="operation_number" class="form-label">{{ __('dobs.operation_ref') }} <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="operation_number" id="operation_number" class="form-control" value="{{ old('operation_number', $operation->operation_number) }}" style="font-family: monospace; font-weight:700; color: var(--color-secondary);" readonly required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="operation_date" class="form-label">{{ __('dobs.operation_date') }} <span style="color: var(--color-danger)">*</span></label>
                    <input type="date" name="operation_date" id="operation_date" class="form-control" value="{{ old('operation_date', $operation->operation_date) }}" required>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">{{ __('dobs.operation_status') }} <span style="color: var(--color-danger)">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Draft" {{ old('status', $operation->status) == 'Draft' ? 'selected' : '' }}>{{ __('dobs.status_draft') }}</option>
                        <option value="Processing" {{ old('status', $operation->status) == 'Processing' ? 'selected' : '' }}>{{ __('dobs.status_processing') }}</option>
                        <option value="Completed" {{ old('status', $operation->status) == 'Completed' ? 'selected' : '' }}>{{ __('dobs.status_completed') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">{{ __('dobs.operation_notes') }}</label>
            <textarea name="notes" id="notes" class="form-control" placeholder="{{ __('dobs.operation_notes_placeholder') }}" rows="3">{{ old('notes', $operation->notes) }}</textarea>
        </div>

        <div class="operation-items-box">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; padding-bottom:0.5rem; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: white;">{{ __('dobs.selected_items') }}</h3>
                <button type="button" class="btn btn-secondary btn-sm" id="btn-add-item" style="color: var(--color-secondary); border-color: rgba(6,182,212,0.3);">
                    <i class="fa-solid fa-plus-circle"></i> {{ __('dobs.add_item_row') }}
                </button>
            </div>

            <div class="table-container">
                <table class="custom-table operation-items-table" id="items-table" style="min-width: 600px;">
                    <thead>
                        <tr>
                            <th style="width: 35%;">{{ __('dobs.col_item') }}</th>
                            <th style="width: 15%;">{{ __('dobs.col_unit_price') }}</th>
                            <th style="width: 12%;">{{ __('dobs.col_quantity') }}</th>
                            <th style="width: 23%;">{{ __('dobs.col_notes') }}</th>
                            <th style="width: 10%;">{{ __('dobs.col_subtotal') }}</th>
                            <th style="width: 5%; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody">
                        @foreach($operation->items as $index => $oItem)
                            <tr data-index="{{ $index }}" data-subtotal="{{ $oItem->pivot->quantity * $oItem->pivot->unit_price }}">
                                <td>
                                    <select name="items[{{ $index }}][item_id]" class="form-control item-select" required>
                                        <option value="">{{ __('dobs.choose_item') }}</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-price="{{ $item->price }}" data-stock="{{ $item->stock }}" {{ $oItem->id == $item->id ? 'selected' : '' }}>
                                                {{ __('dobs.item_option_label', [
                                                    'name' => $item->name,
                                                    'price' => number_format($item->price, 2) . ' ' . __('dobs.currency'),
                                                    'stock' => $item->stock,
                                                ]) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" class="form-control item-price" placeholder="{{ __('dobs.zero_decimal') }}" value="{{ number_format($oItem->pivot->unit_price, 2, '.', '') }}" required>
                                </td>
                                <td>
                                    <input type="number" min="1" name="items[{{ $index }}][quantity]" class="form-control item-qty" placeholder="{{ __('dobs.qty_placeholder') }}" value="{{ $oItem->pivot->quantity }}" required>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][notes]" class="form-control item-notes" placeholder="{{ __('dobs.optional_notes') }}" value="{{ $oItem->pivot->notes }}">
                                </td>
                                <td style="font-weight: 700; color: white; vertical-align: middle;" class="row-subtotal">
                                    {{ number_format($oItem->pivot->quantity * $oItem->pivot->unit_price, 2) }} {{ __('dobs.currency') }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="padding: 0.4rem 0.6rem;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:flex-end; align-items:center; gap:2rem; margin-top:1.5rem; padding-top:1rem; border-top: 1px solid rgba(255,255,255,0.08);">
                <span class="stat-label" style="font-size: 1rem;">{{ __('dobs.calculated_total') }}</span>
                <span style="font-size: 1.6rem; font-weight: 800; color: var(--color-success);" id="operation-total-label">{{ number_format($operation->total_amount, 2) }} {{ __('dobs.currency') }}</span>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('operations.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_changes') }}
            </button>
        </div>
    </form>
</div>

<div style="display:none;" id="items-data-element">
    <option value="">{{ __('dobs.choose_item') }}</option>
    @foreach($items as $item)
        <option value="{{ $item->id }}" data-price="{{ $item->price }}" data-stock="{{ $item->stock }}">
            {{ __('dobs.item_option_label', [
                'name' => $item->name,
                'price' => number_format($item->price, 2) . ' ' . __('dobs.currency'),
                'stock' => $item->stock,
            ]) }}
        </option>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
    window.DOBS_LANG = {
        currency: @json(__('dobs.currency')),
        optional_notes: @json(__('dobs.optional_notes')),
        zero_decimal: @json(__('dobs.zero_decimal')),
        qty_placeholder: @json(__('dobs.qty_placeholder')),
    };

    function formatMoney(amount) {
        const value = parseFloat(amount) || 0;
        return value.toLocaleString('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + DOBS_LANG.currency;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tbody = document.getElementById('items-tbody');
        const btnAdd = document.getElementById('btn-add-item');
        const totalLabel = document.getElementById('operation-total-label');
        const selectOptionsHtml = document.getElementById('items-data-element').innerHTML;

        let rowIndex = {{ $operation->items->count() }};

        tbody.querySelectorAll('tr').forEach(function(row) {
            attachRowListeners(row);
        });

        function addRow() {
            const tr = document.createElement('tr');
            tr.dataset.index = rowIndex;

            tr.innerHTML = `
                <td>
                    <select name="items[${rowIndex}][item_id]" class="form-control item-select" required>
                        ${selectOptionsHtml}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" class="form-control item-price" placeholder="${DOBS_LANG.zero_decimal}" value="${DOBS_LANG.zero_decimal}" required>
                </td>
                <td>
                    <input type="number" min="1" name="items[${rowIndex}][quantity]" class="form-control item-qty" placeholder="${DOBS_LANG.qty_placeholder}" value="1" required>
                </td>
                <td>
                    <input type="text" name="items[${rowIndex}][notes]" class="form-control item-notes" placeholder="${DOBS_LANG.optional_notes}">
                </td>
                <td style="font-weight: 700; color: white; vertical-align: middle;" class="row-subtotal">
                    ${formatMoney(0)}
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="padding: 0.4rem 0.6rem;">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            attachRowListeners(tr);
            rowIndex++;
        }

        function attachRowListeners(row) {
            const selectEl = row.querySelector('.item-select');
            const priceEl = row.querySelector('.item-price');
            const qtyEl = row.querySelector('.item-qty');
            const removeBtn = row.querySelector('.btn-remove-row');

            selectEl.addEventListener('change', function() {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                if (selectedOption && selectedOption.value !== '') {
                    priceEl.value = (parseFloat(selectedOption.dataset.price) || 0).toFixed(2);
                } else {
                    priceEl.value = DOBS_LANG.zero_decimal;
                }
                calculateRowSubtotal(row);
                calculateGrandTotal();
            });

            priceEl.addEventListener('input', function() {
                calculateRowSubtotal(row);
                calculateGrandTotal();
            });

            qtyEl.addEventListener('input', function() {
                calculateRowSubtotal(row);
                calculateGrandTotal();
            });

            removeBtn.addEventListener('click', function() {
                row.remove();
                calculateGrandTotal();
            });

            calculateRowSubtotal(row);
        }

        function calculateRowSubtotal(row) {
            const priceEl = row.querySelector('.item-price');
            const qtyEl = row.querySelector('.item-qty');
            const subtotalLabel = row.querySelector('.row-subtotal');

            const price = parseFloat(priceEl.value) || 0;
            const qty = parseInt(qtyEl.value, 10) || 0;
            const subtotal = price * qty;

            row.dataset.subtotal = subtotal;
            subtotalLabel.textContent = formatMoney(subtotal);
        }

        function calculateGrandTotal() {
            let total = 0;
            tbody.querySelectorAll('tr').forEach(function(row) {
                total += parseFloat(row.dataset.subtotal) || 0;
            });
            totalLabel.textContent = formatMoney(total);
        }

        calculateGrandTotal();
        btnAdd.addEventListener('click', addRow);
    });
</script>
@endsection
