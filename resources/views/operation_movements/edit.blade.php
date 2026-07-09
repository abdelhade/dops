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

        <div class="form-group">
            <label for="operation_id" class="form-label">{{ __('dobs.col_operation') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="operation_id" id="operation_id" class="form-control" required>
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($operations as $operation)
                    <option value="{{ $operation->id }}" {{ old('operation_id', $operationMovement->operation_id) == $operation->id ? 'selected' : '' }}>
                        {{ $operation->operation_number }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="service_id" class="form-label">{{ __('dobs.col_service') }}</label>
            <select name="service_id" id="service_id" class="form-control">
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ old('service_id', $operationMovement->service_id) == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
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
        const operationsData = @json($operationsData);
        const operationSelect = document.getElementById('operation_id');
        const serviceSelect = document.getElementById('service_id');
        const typeSelect = document.getElementById('type');

        // Store original options
        const originalOptions = Array.from(operationSelect.options);

        function filterOperations(isFirstLoad = false) {
            const serviceId = parseInt(serviceSelect.value) || null;
            const type = typeSelect.value;
            const currentValue = isFirstLoad ? '{{ $operationMovement->operation_id }}' : operationSelect.value;

            // Clear current options except the placeholder
            operationSelect.innerHTML = '';
            operationSelect.appendChild(originalOptions[0]); // placeholder

            originalOptions.forEach(opt => {
                if (!opt.value) return;

                const opId = parseInt(opt.value);
                const opData = operationsData.find(o => o.id === opId);
                if (!opData) return;

                let visible = true;

                if (serviceId) {
                    // Must be assigned to this service
                    if (!opData.services.includes(serviceId)) {
                        visible = false;
                    }

                    // For start, end, exit: must have an entry movement
                    if (visible && ['start', 'end', 'exit'].includes(type)) {
                        // Allow current operation to pass even if it's already this one
                        if (!opData.entries[serviceId] && opId !== parseInt('{{ $operationMovement->operation_id }}')) {
                            visible = false;
                        }
                    }
                }

                if (visible) {
                    operationSelect.appendChild(opt);
                }
            });

            // Restore selection if still available
            const optionExists = Array.from(operationSelect.options).some(opt => opt.value === currentValue);
            if (optionExists) {
                operationSelect.value = currentValue;
            } else {
                operationSelect.value = '';
            }
        }

        serviceSelect.addEventListener('change', () => filterOperations(false));
        typeSelect.addEventListener('change', () => filterOperations(false));

        // Run once on load
        filterOperations(true);
    });
</script>
@endsection
