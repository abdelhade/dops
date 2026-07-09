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
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="service_id" class="form-label">{{ __('dobs.col_service') }}</label>
            <select name="service_id" id="service_id" class="form-control">
                <option value="">{{ __('dobs.na') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
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
        const serviceSelect = document.getElementById('service_id');
        const typeSelect = document.getElementById('type');

        // Store original options for both selects
        const originalOperationOptions = Array.from(operationSelect.options);
        const originalServiceOptions = Array.from(serviceSelect.options);

        // Track which select the user changed last to avoid circular filtering
        let lastChangedBy = null;

        function filterServices() {
            const opId = parseInt(operationSelect.value) || null;
            const currentServiceValue = serviceSelect.value;

            // Clear service options
            serviceSelect.innerHTML = '';
            serviceSelect.appendChild(originalServiceOptions[0].cloneNode(true)); // placeholder

            originalServiceOptions.forEach(opt => {
                if (!opt.value) return;

                const sId = parseInt(opt.value);
                let visible = true;

                if (opId) {
                    const opData = operationsData.find(o => o.id === opId);
                    if (opData) {
                        const assignedServices = opData.services.map(Number);
                        if (!assignedServices.includes(sId)) {
                            visible = false;
                        }
                    }
                }

                if (visible) {
                    serviceSelect.appendChild(opt.cloneNode(true));
                }
            });

            // Restore selection if still available
            const optionExists = Array.from(serviceSelect.options).some(opt => opt.value === currentServiceValue);
            serviceSelect.value = optionExists ? currentServiceValue : '';
        }

        function filterOperations() {
            const serviceId = parseInt(serviceSelect.value) || null;
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

                if (serviceId) {
                    // Must be assigned to this service
                    const assignedServices = opData.services.map(Number);
                    if (!assignedServices.includes(serviceId)) {
                        visible = false;
                    }

                    // For start, end, exit: must have an entry movement
                    if (visible && ['start', 'end', 'exit'].includes(type)) {
                        if (!opData.entries[serviceId]) {
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
        }

        // When operation changes: filter services, then re-filter operations
        operationSelect.addEventListener('change', function () {
            lastChangedBy = 'operation';
            filterServices();
            // Don't re-filter operations when operation itself was changed
        });

        // When service changes: filter operations
        serviceSelect.addEventListener('change', function () {
            lastChangedBy = 'service';
            filterOperations();
        });

        // When type changes: re-filter operations
        typeSelect.addEventListener('change', function () {
            filterOperations();
        });
    });
</script>
@endsection
