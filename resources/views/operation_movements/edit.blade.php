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

        <div class="form-group d-none">
            <label for="operation_status_id" class="form-label">حالات العمليات</label>
            <select name="operation_status_id" id="operation_status_id" class="form-control">
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ old('operation_status_id', $operationMovement->operation_status_id) == $status->id ? 'selected' : '' }}>
                        {{ $status->name }}
                    </option>
                @endforeach
            </select>
        </div>

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
        const operationSelect = document.getElementById('operation_id');
        const statusSelect = document.getElementById('operation_status_id');

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

        // Initial filtering
        if (statusSelect.value) {
            statusSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
