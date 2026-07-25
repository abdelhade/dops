@extends('layouts.app')

@section('title', __('dobs.edit_operation_movement'))

@section('header_title', __('dobs.edit_operation_movement'))
@section('header_subtitle', __('dobs.operation_movements_subtitle'))

@section('header_actions')
<a href="{{ route('operation-movements.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('operation-movements.update', $operationMovement->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="form-label d-block text-muted small mb-2">المراحل المسموحة لك</label>
            <div class="d-flex flex-wrap gap-2">
                @forelse($statuses as $status)
                    <span class="badge status-filter-badge" data-id="{{ $status->id }}" style="background-color: {{ $status->color ?? '#6c757d' }}; font-size: 0.9rem; padding: 0.5em 0.8em; cursor: pointer; transition: all 0.2s ease;">
                        {{ $status->name }}
                    </span>
                @empty
                    <span class="text-muted small">لا توجد مراحل مسموحة</span>
                @endforelse
            </div>
        </div>

        <input type="hidden" name="operation_status_id" id="operation_status_id" value="{{ old('operation_status_id', $operationMovement->operation_status_id) }}">

        <div class="form-group">
            <label for="operation_id" class="form-label">{{ __('dobs.col_operation') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="operation_id" id="operation_id" class="form-control" required>
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($operations as $operation)
                    <option value="{{ $operation->id }}" data-status-id="{{ $operation->operation_status_id }}" {{ old('operation_id', $operationMovement->operation_id) == $operation->id ? 'selected' : '' }}>
                        {{ $operation->operation_number }}
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
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">آخر حالة</small>
                        <span id="detail_last_status"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">آخر حركة</small>
                        <span id="detail_last_movement"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="type" class="form-label">{{ __('dobs.col_movement_type') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="type" id="type" class="form-control" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ old('type', $operationMovement->type) == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="datetime" class="form-label">{{ __('dobs.col_datetime') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="datetime-local" name="datetime" id="datetime" class="form-control" value="{{ old('datetime', $operationMovement->datetime ? $operationMovement->datetime->format('Y-m-d\TH:i') : '') }}" required>
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
        const detailsCard = document.getElementById('operation_details_card');

        // Backup all options for filtering
        const allOperationOptions = Array.from(operationSelect.options);
        const filterBadges = document.querySelectorAll('.status-filter-badge');

        filterBadges.forEach(badge => {
            badge.addEventListener('click', function() {
                const statusId = this.getAttribute('data-id');
                
                // Toggle if clicked again
                if (statusSelect.value === statusId) {
                    statusSelect.value = '';
                } else {
                    statusSelect.value = statusId;
                }
                
                // Trigger change to run the filtering logic
                statusSelect.dispatchEvent(new Event('change'));
            });
        });

        statusSelect.addEventListener('change', function() {
            const selectedStatusId = this.value;
            const currentSelectedOpId = operationSelect.value;
            
            // Update badge visuals
            filterBadges.forEach(badge => {
                if (badge.getAttribute('data-id') === selectedStatusId) {
                    badge.style.opacity = '1';
                    badge.style.transform = 'scale(1.05)';
                    badge.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
                    badge.style.border = '2px solid white';
                } else {
                    badge.style.opacity = selectedStatusId ? '0.4' : '1';
                    badge.style.transform = 'scale(1)';
                    badge.style.boxShadow = 'none';
                    badge.style.border = 'none';
                }
            });
            
            // Clear current options
            operationSelect.innerHTML = '';
            // Add the default 'N/A' option
            operationSelect.appendChild(allOperationOptions[0]); 
            
            let hasValidSelected = false;

            for (let i = 1; i < allOperationOptions.length; i++) {
                const opt = allOperationOptions[i];
                const optStatusId = opt.getAttribute('data-status-id');
                
                // Show if no status is selected OR if it matches the selected status
                if (!selectedStatusId || optStatusId === selectedStatusId) {
                    operationSelect.appendChild(opt);
                    if (opt.value === currentSelectedOpId) {
                        hasValidSelected = true;
                    }
                }
            }
            
            if (!hasValidSelected) {
                operationSelect.value = '';
            } else {
                operationSelect.value = currentSelectedOpId;
            }
        });

        function getMovementTypeName(type) {
            const types = {
                'entry': '{{ __('dobs.type_entry') }}',
                'start': '{{ __('dobs.type_start') }}',
                'end': '{{ __('dobs.type_end') }}',
                'exit': '{{ __('dobs.type_exit') }}'
            };
            return types[type] || '-';
        }

        function updateDetailsCard(event) {
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
            
            document.getElementById('detail_last_status').textContent = opData.global_latest_status_name || '-';
            document.getElementById('detail_last_movement').textContent = opData.global_latest_movement_type ? getMovementTypeName(opData.global_latest_movement_type) : '-';
            
            // Auto update status if missing
            if (!statusSelect.value) {
                statusSelect.value = opData.operation_status_id;
                filterBadges.forEach(badge => {
                    if (badge.getAttribute('data-id') == opData.operation_status_id) {
                        badge.style.opacity = '1';
                        badge.style.transform = 'scale(1.05)';
                        badge.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
                        badge.style.border = '2px solid white';
                    } else {
                        badge.style.opacity = '0.4';
                        badge.style.transform = 'scale(1)';
                        badge.style.boxShadow = 'none';
                        badge.style.border = 'none';
                    }
                });
            }

            if (event && event.type === 'change') {
                let nextType = 'entry';
                if (opData.latest_movement_type) {
                    if (opData.latest_movement_type === 'entry') nextType = 'start';
                    else if (opData.latest_movement_type === 'start') nextType = 'end';
                    else if (opData.latest_movement_type === 'end') nextType = 'exit';
                    else if (opData.latest_movement_type === 'exit') nextType = 'entry'; 
                }
                typeSelect.value = nextType;
                typeSelect.dispatchEvent(new Event('change'));
            }

            detailsCard.style.display = 'block';
        }

        operationSelect.addEventListener('change', updateDetailsCard);

        // Initial filtering
        if (statusSelect.value) {
            statusSelect.dispatchEvent(new Event('change'));
        }
        
        if (operationSelect.value) {
            updateDetailsCard();
        }
    });
</script>
@endsection
