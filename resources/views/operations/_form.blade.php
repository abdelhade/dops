@php
    use App\Enums\OperationStencil;
    use App\Enums\OperationType;
    use App\Models\Operation;

    $isEdit = isset($operation);
    $op = $operation ?? $op ?? null;
    $defaultTime = old('operation_time', $op?->formattedOperationTime() ?? now()->format('H:i'));
    $selectedType = old('operation_type', $op?->operation_type?->value ?? OperationType::Offset->value);
    $isSilkScreen = $selectedType === OperationType::SilkScreen->value;
    $defaultOpNumber = $isSilkScreen
        ? ($opNumberSilkScreen ?? $opNumber ?? Operation::nextOperationNumber(OperationType::SilkScreen))
        : ($opNumberOffset ?? $opNumber ?? Operation::nextOperationNumber(OperationType::Offset));
@endphp

<div class="operation-form-compact">
    <div class="form-row form-row-4">
        <div class="form-group">
            <label for="operation_type" class="form-label">{{ __('dobs.operation_type') }} <span class="text-required">*</span></label>
            <select name="operation_type" id="operation_type" class="form-control" required>
                @foreach(OperationType::casesForSelect() as $typeOption)
                    <option value="{{ $typeOption->value }}" {{ $selectedType === $typeOption->value ? 'selected' : '' }}>
                        {{ $typeOption->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="operation_number" class="form-label">{{ __('dobs.operation_serial') }} <span class="text-required">*</span></label>
            <input
                type="text"
                name="operation_number"
                id="operation_number"
                class="form-control form-control-mono"
                value="{{ old('operation_number', $op?->operation_number ?? $defaultOpNumber) }}"
                data-next-offset="{{ $opNumberOffset ?? Operation::nextOperationNumber(OperationType::Offset) }}"
                data-next-silk-screen="{{ $opNumberSilkScreen ?? Operation::nextOperationNumber(OperationType::SilkScreen) }}"
                required
            >
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
    </div>

    <div class="form-row form-row-4">
        <div class="form-group">
            <label for="operation_status_id" class="form-label">{{ __('dobs.operation_status') }} <span class="text-required">*</span></label>
            <select name="operation_status_id" id="operation_status_id" class="form-control" data-allow-create="operation_status" required>
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

    <div class="form-row form-row-2">
        <div class="form-group">
            <label for="client_id" class="form-label">{{ __('dobs.operation_client') }}</label>
            <select
                name="client_id"
                id="client_id"
                class="form-control"
                data-allow-create="client"
            >
                <option value="">{{ __('dobs.select_client') }}</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string) old('client_id', $op?->client_id) === (string) $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="related_sales_order_number" class="form-label">{{ __('dobs.operation_related_sales_order_number') }}</label>
            <input
                type="text"
                name="related_sales_order_number"
                id="related_sales_order_number"
                class="form-control form-control-mono"
                value="{{ old('related_sales_order_number', $op?->related_sales_order_number) }}"
                placeholder="{{ __('dobs.operation_related_sales_order_number_placeholder') }}"
            >
        </div>
    </div>

    <div class="form-row form-row-product">
        <div class="form-group">
            <label for="item_id" class="form-label">{{ __('dobs.operation_product_1') }} <span class="text-required">*</span></label>
            <select name="item_id" id="item_id" class="form-control" data-allow-create="item" required>
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

    <div class="form-row form-row-3" id="operation-suppliers-row">
        <div class="form-group">
            <label for="printing_supplier_id" class="form-label">{{ __('dobs.operation_printing_press') }}</label>
            <select name="printing_supplier_id" id="printing_supplier_id" class="form-control" data-allow-create="supplier">
                <option value="">{{ __('dobs.select_supplier') }}</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ (string) old('printing_supplier_id', $op?->printing_supplier_id) === (string) $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group op-field-offset-only">
            <label for="ctp_supplier_id" class="form-label">{{ __('dobs.operation_ctp') }}</label>
            <select name="ctp_supplier_id" id="ctp_supplier_id" class="form-control" data-allow-create="supplier">
                <option value="">{{ __('dobs.select_supplier') }}</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ (string) old('ctp_supplier_id', $op?->ctp_supplier_id) === (string) $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="paper_type_id" class="form-label">{{ __('dobs.operation_paper_material') }}</label>
            <select name="paper_type_id" id="paper_type_id" class="form-control" data-allow-create="paper_type">
                <option value="">{{ __('dobs.select_paper_type') }}</option>
                @foreach($paperTypes as $paperType)
                    <option value="{{ $paperType->id }}" {{ (string) old('paper_type_id', $op?->paper_type_id) === (string) $paperType->id ? 'selected' : '' }}>
                        {{ $paperType->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row op-field-silk-screen-only" id="operation-stencil-row" @if(!$isSilkScreen) hidden @endif>
        <div class="form-group">
            <label for="stencil" class="form-label">{{ __('dobs.operation_stencil') }} <span class="text-required">*</span></label>
            <select name="stencil" id="stencil" class="form-control" @if(!$isSilkScreen) disabled @endif>
                <option value="">{{ __('dobs.select_stencil') }}</option>
                @foreach(OperationStencil::casesForSelect() as $stencilOption)
                    <option value="{{ $stencilOption->value }}" {{ old('stencil', $op?->stencil?->value) === $stencilOption->value ? 'selected' : '' }}>
                        {{ $stencilOption->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row form-row-3 op-field-offset-only" id="operation-offset-metrics-row" @if($isSilkScreen) hidden @endif>
        <div class="form-group">
            <label for="job_size" class="form-label">{{ __('dobs.operation_job_size') }}</label>
            <input type="number" step="0.01" min="0" name="job_size" id="job_size" class="form-control" value="{{ old('job_size', $op?->job_size) }}" @if($isSilkScreen) disabled @endif>
        </div>

        <div class="form-group">
            <label for="pull_count" class="form-label">{{ __('dobs.operation_pull_count') }}</label>
            <input type="number" min="0" step="1" name="pull_count" id="pull_count" class="form-control" value="{{ old('pull_count', $op?->pull_count) }}" @if($isSilkScreen) disabled @endif>
        </div>

        <div class="form-group">
            <label for="quantity_per_sheet" class="form-label">{{ __('dobs.operation_quantity_per_sheet') }}</label>
            <input type="number" min="0" step="1" name="quantity_per_sheet" id="quantity_per_sheet" class="form-control form-control-mono" value="{{ old('quantity_per_sheet', $op?->quantity_per_sheet) }}" readonly tabindex="-1" @if($isSilkScreen) disabled @endif>
        </div>
    </div>

    <div class="form-row op-field-offset-only" id="operation-service-row" @if($isSilkScreen) hidden @endif>
        <div class="form-group">
            <label for="service_1_id" class="form-label">{{ __('dobs.operation_service_1') }}</label>
            <select name="service_1_id" id="service_1_id" class="form-control" data-allow-create="service" @if($isSilkScreen) disabled @endif>
                <option value="">{{ __('dobs.select_service') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ (string) old('service_1_id', $op?->service_1_id) === (string) $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
