@extends('layouts.app')

@section('title', __('dobs.create_operation'))

@section('header_title', __('dobs.create_operation'))
@section('header_subtitle', __('dobs.create_operation_subtitle'))

@section('header_actions')
<a href="{{ route('operations.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 1000px; margin: 0 auto;">
    <form action="{{ route('operations.store') }}" method="POST" id="operation-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="operation_number" class="form-label">{{ __('dobs.operation_ref') }} <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="operation_number" id="operation_number" class="form-control" value="{{ old('operation_number', $opNumber) }}" style="font-family: monospace; font-weight:700; color: var(--color-secondary);" readonly required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="operation_date" class="form-label">{{ __('dobs.operation_date') }} <span style="color: var(--color-danger)">*</span></label>
                    <input type="date" name="operation_date" id="operation_date" class="form-control" value="{{ old('operation_date', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">{{ __('dobs.operation_status') }} <span style="color: var(--color-danger)">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Draft" {{ old('status') == 'Draft' ? 'selected' : '' }}>{{ __('dobs.status_draft') }}</option>
                        <option value="Processing" {{ old('status') == 'Processing' ? 'selected' : '' }}>{{ __('dobs.status_processing') }}</option>
                        <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>{{ __('dobs.status_completed') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">{{ __('dobs.operation_notes') }}</label>
            <textarea name="notes" id="notes" class="form-control" placeholder="{{ __('dobs.operation_notes_placeholder') }}" rows="3">{{ old('notes') }}</textarea>
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
                    <tbody id="items-tbody"></tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:flex-end; align-items:center; gap:2rem; margin-top:1.5rem; padding-top:1rem; border-top: 1px solid rgba(255,255,255,0.08);">
                <span class="stat-label" style="font-size: 1rem;">{{ __('dobs.calculated_total') }}</span>
                <span style="font-size: 1.6rem; font-weight: 800; color: var(--color-success);" id="operation-total-label">0.00 {{ __('dobs.currency') }}</span>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('operations.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_operation') }}
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

        let rowIndex = 0;

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

            const selectEl = tr.querySelector('.item-select');
            const priceEl = tr.querySelector('.item-price');
            const qtyEl = tr.querySelector('.item-qty');
            const removeBtn = tr.querySelector('.btn-remove-row');

            selectEl.addEventListener('change', function() {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                if (selectedOption && selectedOption.value !== '') {
                    priceEl.value = (parseFloat(selectedOption.dataset.price) || 0).toFixed(2);
                } else {
                    priceEl.value = DOBS_LANG.zero_decimal;
                }
                calculateRowSubtotal(tr);
                calculateGrandTotal();
            });

            priceEl.addEventListener('input', function() {
                calculateRowSubtotal(tr);
                calculateGrandTotal();
            });

            qtyEl.addEventListener('input', function() {
                calculateRowSubtotal(tr);
                calculateGrandTotal();
            });

            removeBtn.addEventListener('click', function() {
                tr.remove();
                calculateGrandTotal();
            });

            rowIndex++;
        }

        function calculateRowSubtotal(row) {
            const priceEl = row.querySelector('.item-price');
            const qtyEl = row.querySelector('.item-qty');
            const subtotalLabel = row.querySelector('.row-subtotal');

            const price = parseFloat(priceEl.value) || 0;
            const qty = parseInt(qtyEl.value, 10) || 0;
            const subtotal = price * qty;

            subtotalLabel.textContent = formatMoney(subtotal);
            row.dataset.subtotal = subtotal;
        }

        function calculateGrandTotal() {
            let total = 0;
            tbody.querySelectorAll('tr').forEach(function(row) {
                total += parseFloat(row.dataset.subtotal) || 0;
            });
            totalLabel.textContent = formatMoney(total);
        }

        addRow();
        btnAdd.addEventListener('click', addRow);
    });
</script>
@endsection
