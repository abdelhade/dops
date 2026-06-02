@php
    $isEdit = isset($operation);
    $op = $operation ?? null;
    $defaultTime = old('operation_time', $op?->formattedOperationTime() ?? now()->format('H:i'));
@endphp

<div class="form-group">
    <label for="operation_number" class="form-label">{{ __('dobs.operation_serial') }} <span style="color: var(--color-danger)">*</span></label>
    <input type="text" name="operation_number" id="operation_number" class="form-control" value="{{ old('operation_number', $op?->operation_number ?? $opNumber) }}" style="font-family: monospace; font-weight:700; color: var(--color-secondary);" readonly required>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="operation_date" class="form-label">{{ __('dobs.operation_date') }} <span style="color: var(--color-danger)">*</span></label>
        <input type="date" name="operation_date" id="operation_date" class="form-control" value="{{ old('operation_date', $op?->operation_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>

    <div class="form-group">
        <label for="operation_time_display" class="form-label">{{ __('dobs.operation_current_time') }}</label>
        <input type="text" id="operation_time_display" class="form-control" value="{{ $defaultTime }}" readonly style="font-family: monospace;">
        <input type="hidden" name="operation_time" id="operation_time" value="{{ $defaultTime }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="item_id" class="form-label">{{ __('dobs.operation_product_1') }} <span style="color: var(--color-danger)">*</span></label>
        <select name="item_id" id="item_id" class="form-control" required>
            <option value="">{{ __('dobs.choose_item') }}</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" {{ (string) old('item_id', $op?->item_id) === (string) $item->id ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="quantity" class="form-label">{{ __('dobs.col_quantity') }} <span style="color: var(--color-danger)">*</span></label>
        <input type="number" min="1" name="quantity" id="quantity" class="form-control" value="{{ old('quantity', $op?->quantity ?? 1) }}" required>
    </div>
</div>

<div class="form-group">
    <label for="statement" class="form-label">{{ __('dobs.operation_statement') }}</label>
    <textarea name="statement" id="statement" class="form-control" rows="3" placeholder="{{ __('dobs.operation_statement_placeholder') }}">{{ old('statement', $op?->statement ?? $op?->notes) }}</textarea>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="printing_supplier_id" class="form-label">{{ __('dobs.operation_printing_press') }}</label>
        <select name="printing_supplier_id" id="printing_supplier_id" class="form-control">
            <option value="">{{ __('dobs.select_supplier') }}</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ (string) old('printing_supplier_id', $op?->printing_supplier_id) === (string) $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="ctp_supplier_id" class="form-label">{{ __('dobs.operation_ctp') }}</label>
        <select name="ctp_supplier_id" id="ctp_supplier_id" class="form-control">
            <option value="">{{ __('dobs.select_supplier') }}</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ (string) old('ctp_supplier_id', $op?->ctp_supplier_id) === (string) $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="color_count" class="form-label">{{ __('dobs.operation_color_count') }} <span style="color: var(--color-danger)">*</span></label>
        <select name="color_count" id="color_count" class="form-control" required>
            @for($c = 1; $c <= 10; $c++)
                <option value="{{ $c }}" {{ (int) old('color_count', $op?->color_count ?? 1) === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endfor
        </select>
    </div>

    <div class="form-group">
        <label for="material_id" class="form-label">{{ __('dobs.operation_paper_material') }}</label>
        <select name="material_id" id="material_id" class="form-control">
            <option value="">{{ __('dobs.select_material') }}</option>
            @foreach($materials as $material)
                <option value="{{ $material->id }}" {{ (string) old('material_id', $op?->material_id) === (string) $material->id ? 'selected' : '' }}>
                    {{ $material->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row" style="grid-template-columns: repeat(3, 1fr);">
    <div class="form-group">
        <label for="job_size" class="form-label">{{ __('dobs.operation_job_size') }}</label>
        <input type="number" step="0.01" min="0" name="job_size" id="job_size" class="form-control" value="{{ old('job_size', $op?->job_size) }}">
    </div>

    <div class="form-group">
        <label for="pull_count" class="form-label">{{ __('dobs.operation_pull_count') }}</label>
        <input type="number" min="0" step="1" name="pull_count" id="pull_count" class="form-control" value="{{ old('pull_count', $op?->pull_count) }}">
    </div>

    <div class="form-group">
        <label for="quantity_per_sheet" class="form-label">{{ __('dobs.operation_quantity_per_sheet') }}</label>
        <input type="number" min="0" step="1" name="quantity_per_sheet" id="quantity_per_sheet" class="form-control" value="{{ old('quantity_per_sheet', $op?->quantity_per_sheet) }}">
    </div>
</div>

<div class="form-row" style="grid-template-columns: repeat(3, 1fr);">
    <div class="form-group">
        <label for="service_1_id" class="form-label">{{ __('dobs.operation_service_1') }}</label>
        <select name="service_1_id" id="service_1_id" class="form-control">
            <option value="">{{ __('dobs.select_service') }}</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) old('service_1_id', $op?->service_1_id) === (string) $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="service_2_id" class="form-label">{{ __('dobs.operation_service_2') }}</label>
        <select name="service_2_id" id="service_2_id" class="form-control">
            <option value="">{{ __('dobs.select_service') }}</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) old('service_2_id', $op?->service_2_id) === (string) $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="service_3_id" class="form-label">{{ __('dobs.operation_service_3') }}</label>
        <select name="service_3_id" id="service_3_id" class="form-control">
            <option value="">{{ __('dobs.select_service') }}</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) old('service_3_id', $op?->service_3_id) === (string) $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label for="status" class="form-label">{{ __('dobs.operation_status') }} <span style="color: var(--color-danger)">*</span></label>
    <select name="status" id="status" class="form-control" required>
        <option value="Draft" {{ old('status', $op?->status ?? 'Draft') == 'Draft' ? 'selected' : '' }}>{{ __('dobs.status_draft') }}</option>
        <option value="Processing" {{ old('status', $op?->status ?? '') == 'Processing' ? 'selected' : '' }}>{{ __('dobs.status_processing') }}</option>
        <option value="Completed" {{ old('status', $op?->status ?? '') == 'Completed' ? 'selected' : '' }}>{{ __('dobs.status_completed') }}</option>
    </select>
</div>
