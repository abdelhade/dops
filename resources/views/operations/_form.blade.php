@php
    $isEdit = isset($operation);
    $op = $operation ?? $op ?? null;
    $defaultTime = old('operation_time', $op?->formattedOperationTime() ?? now()->format('H:i'));
@endphp

<div class="operation-form-compact">
    <div class="form-row form-row-4">
        <div class="form-group">
            <label for="operation_number" class="form-label">{{ __('dobs.operation_serial') }} <span class="text-required">*</span></label>
            <input type="text" name="operation_number" id="operation_number" class="form-control form-control-mono" value="{{ old('operation_number', $op?->operation_number ?? $opNumber) }}" readonly required>
        </div>

        <div class="form-group">
            <label for="operation_date" class="form-label">{{ __('dobs.operation_date') }} <span class="text-required">*</span></label>
            <input type="date" name="operation_date" id="operation_date" class="form-control" value="{{ old('operation_date', $op?->operation_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
        </div>

        <div class="form-group">
            <label for="operation_time" class="form-label">{{ __('dobs.operation_current_time') }}</label>
            @if($isEdit)
                <input type="time" name="operation_time" id="operation_time" class="form-control form-control-mono" value="{{ $defaultTime }}" required>
            @else
                <div class="form-static-value" id="operation_time_display">{{ $defaultTime }}</div>
                <input type="hidden" name="operation_time" id="operation_time" value="{{ $defaultTime }}">
            @endif
        </div>

        <div class="form-group">
            <label for="operation_status_id" class="form-label">{{ __('dobs.operation_status') }} <span class="text-required">*</span></label>
            <select name="operation_status_id" id="operation_status_id" class="form-control" required>
                @if(isset($operationStatuses) && $operationStatuses->count() > 0)
                    @foreach($operationStatuses as $statusOpt)
                        <option value="{{ $statusOpt->id }}" {{ (string) old('operation_status_id', $op?->operation_status_id) === (string) $statusOpt->id ? 'selected' : '' }}>
                            {{ $statusOpt->name }}
                        </option>
                    @endforeach
                @else
                    <option value="" disabled selected>{{ __('dobs.no_statuses') }}</option>
                @endif
            </select>
        </div>
    </div>

    <div class="form-row form-row-product">
        <div class="form-group">
            <label for="item_id" class="form-label">{{ __('dobs.operation_product_1') }} <span class="text-required">*</span></label>
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
            <label for="quantity" class="form-label">{{ __('dobs.col_quantity') }} <span class="text-required">*</span></label>
            <input type="number" min="1" name="quantity" id="quantity" class="form-control" value="{{ old('quantity', $op?->quantity ?? 1) }}" required>
        </div>

        <div class="form-group">
            <label for="color_count" class="form-label">{{ __('dobs.operation_color_count') }} <span class="text-required">*</span></label>
            <select name="color_count" id="color_count" class="form-control" required>
                @for($c = 1; $c <= 10; $c++)
                    <option value="{{ $c }}" {{ (int) old('color_count', $op?->color_count ?? 1) === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="statement" class="form-label">{{ __('dobs.operation_statement') }}</label>
        <textarea name="statement" id="statement" class="form-control form-control-compact" rows="2" placeholder="{{ __('dobs.operation_statement_placeholder') }}">{{ old('statement', $op?->statement ?? $op?->notes) }}</textarea>
    </div>

    <div class="form-row form-row-3">
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

    <div class="form-row form-row-3">
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
            <input type="number" min="0" step="1" name="quantity_per_sheet" id="quantity_per_sheet" class="form-control form-control-mono" value="{{ old('quantity_per_sheet', $op?->quantity_per_sheet) }}" readonly tabindex="-1">
        </div>
    </div>

    <div class="form-row form-row-3">
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
</div>
