@extends('layouts.app')

@section('title', __('dobs.create_operation_movement'))

@section('header_title', __('dobs.create_operation_movement'))
@section('header_subtitle', __('dobs.operation_movements_subtitle'))

@section('header_actions')
<a href="{{ route('operation-movements.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('operation-movements.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="operation_id" class="form-label">{{ __('dobs.col_operation') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="operation_id" id="operation_id" class="form-control" required>
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($operations as $operation)
                    <option value="{{ $operation->id }}" {{ old('operation_id') == $operation->id ? 'selected' : '' }}>
                        {{ $operation->operation_number }} 
                        @if($operation->client) - {{ $operation->client->name }} @endif 
                        @if($operation->item) - {{ $operation->item->name }} @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div id="operation_details_card" class="card mt-3 mb-4" style="display: none; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">
            <div class="card-body p-3">
                <h6 class="card-title mb-2" style="color: var(--color-primary); font-weight: bold;" id="detail_op_number"></h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">{{ __('dobs.operation_client') }}</small>
                        <span id="detail_client"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">المنتج</small>
                        <span id="detail_item"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">{{ __('dobs.col_quantity') }}</small>
                        <span id="detail_qty"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">{{ __('dobs.operation_statement') }}</small>
                        <span id="detail_statement"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="operation_status_id" class="form-label">حالات العمليات</label>
            <select name="operation_status_id" id="operation_status_id" class="form-control">
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ old('operation_status_id') == $status->id ? 'selected' : '' }}>
                        {{ $status->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="type" class="form-label">{{ __('dobs.col_movement_type') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="type" id="type" class="form-control" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="next_status_group" style="display: none;">
            <label for="next_status_id" class="form-label">الحالة المُحَوّل إليها (دخول) <span style="color: var(--color-danger)">*</span></label>
            <select name="next_status_id" id="next_status_id" class="form-control">
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ old('next_status_id') == $status->id ? 'selected' : '' }}>
                        {{ $status->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="datetime" class="form-label">{{ __('dobs.col_datetime') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="datetime-local" name="datetime" id="datetime" class="form-control" value="{{ old('datetime', now()->format('Y-m-d\TH:i')) }}" required>
        </div>

        <div class="form-actions">
            <a href="{{ route('operation-movements.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save') }}
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const operationsData = @json($operationsData);
        const operationSelect = document.getElementById('operation_id');
        const statusSelect = document.getElementById('operation_status_id');
        const typeSelect = document.getElementById('type');
        const nextStatusGroup = document.getElementById('next_status_group');
        const nextStatusSelect = document.getElementById('next_status_id');
        const detailsCard = document.getElementById('operation_details_card');

        // Store original options for both selects
        const originalOperationOptions = Array.from(operationSelect.options);
        const originalStatusOptions = Array.from(statusSelect.options);

        function updateDetailsCard() {
            const opId = parseInt(operationSelect.value);
            if (!opId) {
                detailsCard.style.display = 'none';
                return;
            }
            const opData = operationsData.find(o => o.id === opId);
            if (!opData) {
                detailsCard.style.display = 'none';
                return;
            }
            
            document.getElementById('detail_op_number').textContent = opData.operation_number;
            document.getElementById('detail_client').textContent = opData.client_name || '-';
            document.getElementById('detail_item').textContent = opData.item_name || '-';
            document.getElementById('detail_qty').textContent = opData.quantity || '-';
            document.getElementById('detail_statement').textContent = opData.statement || '-';
            
            detailsCard.style.display = 'block';
        }

        operationSelect.addEventListener('change', updateDetailsCard);

        function filterOperations() {
            const statusId = parseInt(statusSelect.value) || null;
            const type = typeSelect.value;
            const currentValue = operationSelect.value;

            // Clear current options except the placeholder
            operationSelect.innerHTML = '';
            operationSelect.appendChild(originalOperationOptions[0].cloneNode(true)); // placeholder

            originalOperationOptions.forEach(opt => {
                if (!opt.value) return;

                const opId = parseInt(opt.value);
                const opData = operationsData.find(o => o.id === opId);
                if (!opData) return;

                let visible = true;

                if (statusId) {
                    // For start, end, exit: must have an entry movement
                    if (visible && ['start', 'end', 'exit'].includes(type)) {
                        if (!opData.entries[statusId]) {
                            visible = false;
                        }
                    }
                }

                if (visible) {
                    operationSelect.appendChild(opt.cloneNode(true));
                }
            });

            // Restore selection if still available
            const optionExists = Array.from(operationSelect.options).some(opt => opt.value === currentValue);
            operationSelect.value = optionExists ? currentValue : '';
            updateDetailsCard();
        }

        // When status changes: filter operations
        statusSelect.addEventListener('change', function () {
            filterOperations();
        });

        // When type changes: re-filter operations and toggle next status
        typeSelect.addEventListener('change', function () {
            filterOperations();
            if (this.value === 'exit') {
                nextStatusGroup.style.display = 'block';
                nextStatusSelect.required = true;
            } else {
                nextStatusGroup.style.display = 'none';
                nextStatusSelect.required = false;
                nextStatusSelect.value = '';
            }
        });

        // Initial setup on page load
        if (typeSelect.value === 'exit') {
            nextStatusGroup.style.display = 'block';
            nextStatusSelect.required = true;
        }
        if (operationSelect.value) {
            updateDetailsCard();
        }
    });
</script>
@endsection
