@php
    use App\Enums\OperationSilkUnit;
    use App\Enums\OperationStencil;

    $filterAction = $filterAction ?? route('reports.paper-materials-summary');
    $clearFiltersUrl = $clearFiltersUrl ?? route('reports.paper-materials-summary');
    $reportType = $reportType ?? 'offset';
    $isGeneralReport = $reportType === 'general';
    $defaultDateFrom = request('date_from', now()->startOfMonth()->format('Y-m-d'));
    $defaultDateTo = request('date_to', now()->format('Y-m-d'));
    $advancedFilterKeys = $isGeneralReport
        ? [
            'operation_number', 'related_sales_order_number', 'client_id', 'operation_kind_id',
            'item_id', 'quantity', 'silk_unit', 'color_count', 'statement',
            'printing_supplier_id', 'paper_type_id', 'stencil', 'operation_status_id',
        ]
        : [
            'operation_number', 'related_sales_order_number', 'client_id', 'item_id', 'quantity', 'statement',
            'printing_supplier_id', 'ctp_supplier_id', 'color_count', 'paper_type_id',
            'job_size', 'pull_count', 'quantity_per_sheet', 'service_1_id',
            'operation_status_id', 'notes',
        ];
    $hasAdvancedFilters = collect($advancedFilterKeys)->contains(fn ($key) => request()->filled($key));
@endphp

<form method="GET" action="{{ $filterAction }}" class="report-filters-form">
    <input type="hidden" name="applied" value="1">

    <div class="report-filters-primary">
        <div class="report-filters-primary-fields">
            <div class="form-group report-filter-field report-filter-field-dates">
                <label class="form-label">{{ __('dobs.date_from') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $defaultDateFrom }}" required>
            </div>

            <div class="form-group report-filter-field report-filter-field-dates">
                <label class="form-label">{{ __('dobs.date_to') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $defaultDateTo }}" required>
            </div>

            <div class="form-group report-filter-field report-filter-field-search">
                <label class="form-label">{{ __('dobs.report_global_search') }}</label>
                <div class="report-filter-search-wrap">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        type="text"
                        name="search"
                        class="form-control form-control-sm"
                        value="{{ request('search') }}"
                        placeholder="{{ __('dobs.report_global_search_placeholder') }}"
                    >
                </div>
            </div>
        </div>

        <div class="report-filters-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> {{ __('dobs.report_show_results') }}
            </button>
            <a href="{{ $clearFiltersUrl }}" class="btn btn-secondary" title="{{ __('dobs.clear_filters') }}">
                <i class="fa-solid fa-rotate-left"></i> {{ __('dobs.clear_filters') }}
            </a>
            @if ($showPrintButton ?? false)
                <button type="button" class="btn btn-secondary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> {{ __('dobs.print') }}
                </button>
            @endif
        </div>
    </div>

    <details class="report-filters-advanced" @if($hasAdvancedFilters) open @endif>
        <summary class="report-filters-advanced-toggle">
            <i class="fa-solid fa-chevron-down report-filters-advanced-chevron" aria-hidden="true"></i>
            {{ __('dobs.report_advanced_filters') }}
        </summary>

        <div class="report-filters-advanced-grid">
            @if($isGeneralReport)
                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_serial') }}</label>
                    <input type="text" name="operation_number" class="form-control form-control-sm" value="{{ request('operation_number') }}" placeholder="{{ __('dobs.report_search_by_serial') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_related_sales_order_number') }}</label>
                    <input type="text" name="related_sales_order_number" class="form-control form-control-sm" value="{{ request('related_sales_order_number') }}" placeholder="{{ __('dobs.operation_related_sales_order_number') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_client') }}</label>
                    <select name="client_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_kind') }}</label>
                    <select name="operation_kind_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($operationKinds as $kind)
                            <option value="{{ $kind->id }}" @selected(request('operation_kind_id') == $kind->id)>{{ $kind->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_silk_final_product') }}</label>
                    <select name="item_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" @selected(request('item_id') == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.col_quantity') }}</label>
                    <input type="number" min="0" step="1" name="quantity" class="form-control form-control-sm" value="{{ request('quantity') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_silk_unit') }}</label>
                    <select name="silk_unit" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach(OperationSilkUnit::casesForSelect() as $unitOption)
                            <option value="{{ $unitOption->value }}" @selected(request('silk_unit') === $unitOption->value)>{{ $unitOption->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_color_count') }}</label>
                    <select name="color_count" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @for($c = 1; $c <= 10; $c++)
                            <option value="{{ $c }}" @selected(request('color_count') == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group report-filter-field report-filter-field-wide">
                    <label class="form-label">{{ __('dobs.operation_statement') }}</label>
                    <input type="text" name="statement" class="form-control form-control-sm" value="{{ request('statement') }}" placeholder="{{ __('dobs.operation_statement_placeholder') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_silk_supplier') }}</label>
                    <div class="custom-multiselect-container" id="printing-supplier-multiselect-general">
                        <div class="custom-multiselect-trigger form-control form-control-sm" tabindex="0">
                            <span class="custom-multiselect-label" data-default-text="{{ __('dobs.filter_all') }}">{{ __('dobs.filter_all') }}</span>
                            <i class="fa-solid fa-chevron-down custom-multiselect-chevron"></i>
                        </div>
                        <div class="custom-multiselect-dropdown">
                            <div class="custom-multiselect-search-box">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" class="custom-multiselect-search-input" placeholder="{{ __('dobs.select_search_placeholder') }}">
                            </div>
                            <div class="custom-multiselect-actions-checkbox" style="padding-bottom: 0.4rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--bs-border-color, #dee2e6);">
                                <label class="custom-multiselect-option-label select-all-label" style="font-weight: 600;">
                                    <input type="checkbox" class="custom-multiselect-select-all-checkbox" style="width: 15px; height: 15px; cursor: pointer; margin: 0; margin-top: 0.15rem; flex-shrink: 0;">
                                    <span class="option-text">{{ __('dobs.bulk_select_all') }}</span>
                                </label>
                            </div>
                            <div class="custom-multiselect-options">
                                @foreach($suppliers as $supplier)
                                    @php
                                        $isSelected = is_array(request('printing_supplier_id'))
                                            ? in_array($supplier->id, request('printing_supplier_id'))
                                            : request('printing_supplier_id') == $supplier->id;
                                    @endphp
                                    <label class="custom-multiselect-option-label" data-search-name="{{ mb_strtolower($supplier->name, 'UTF-8') }}">
                                        <input type="checkbox" name="printing_supplier_id[]" value="{{ $supplier->id }}" class="custom-multiselect-checkbox" @checked($isSelected)>
                                        <span class="option-text">{{ $supplier->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_paper_material') }}</label>
                    <select name="paper_type_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($paperTypes as $paperType)
                            <option value="{{ $paperType->id }}" @selected(request('paper_type_id') == $paperType->id)>{{ $paperType->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_silk_print_preparations') }}</label>
                    <select name="stencil" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach(OperationStencil::casesForSelect() as $stencilOption)
                            <option value="{{ $stencilOption->value }}" @selected(request('stencil') === $stencilOption->value)>{{ $stencilOption->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_status') }}</label>
                    <select name="operation_status_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($operationStatuses as $status)
                            <option value="{{ $status->id }}" @selected(request('operation_status_id') == $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_serial') }}</label>
                    <input type="text" name="operation_number" class="form-control form-control-sm" value="{{ request('operation_number') }}" placeholder="{{ __('dobs.report_search_by_serial') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_related_sales_order_number') }}</label>
                    <input type="text" name="related_sales_order_number" class="form-control form-control-sm" value="{{ request('related_sales_order_number') }}" placeholder="{{ __('dobs.operation_related_sales_order_number') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_client') }}</label>
                    <select name="client_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.log_field_item_id') }}</label>
                    <select name="item_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" @selected(request('item_id') == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.col_quantity') }}</label>
                    <input type="number" min="0" step="1" name="quantity" class="form-control form-control-sm" value="{{ request('quantity') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_statement') }}</label>
                    <input type="text" name="statement" class="form-control form-control-sm" value="{{ request('statement') }}" placeholder="{{ __('dobs.operation_statement_placeholder') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_printing_press') }}</label>
                    <div class="custom-multiselect-container" id="printing-supplier-multiselect-offset">
                        <div class="custom-multiselect-trigger form-control form-control-sm" tabindex="0">
                            <span class="custom-multiselect-label" data-default-text="{{ __('dobs.filter_all') }}">{{ __('dobs.filter_all') }}</span>
                            <i class="fa-solid fa-chevron-down custom-multiselect-chevron"></i>
                        </div>
                        <div class="custom-multiselect-dropdown">
                            <div class="custom-multiselect-search-box">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" class="custom-multiselect-search-input" placeholder="{{ __('dobs.select_search_placeholder') }}">
                            </div>
                            <div class="custom-multiselect-actions-checkbox" style="padding-bottom: 0.4rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--bs-border-color, #dee2e6);">
                                <label class="custom-multiselect-option-label select-all-label" style="font-weight: 600;">
                                    <input type="checkbox" class="custom-multiselect-select-all-checkbox" style="width: 15px; height: 15px; cursor: pointer; margin: 0; margin-top: 0.15rem; flex-shrink: 0;">
                                    <span class="option-text">{{ __('dobs.bulk_select_all') }}</span>
                                </label>
                            </div>
                            <div class="custom-multiselect-options">
                                @foreach($suppliers as $supplier)
                                    @php
                                        $isSelected = is_array(request('printing_supplier_id'))
                                            ? in_array($supplier->id, request('printing_supplier_id'))
                                            : request('printing_supplier_id') == $supplier->id;
                                    @endphp
                                    <label class="custom-multiselect-option-label" data-search-name="{{ mb_strtolower($supplier->name, 'UTF-8') }}">
                                        <input type="checkbox" name="printing_supplier_id[]" value="{{ $supplier->id }}" class="custom-multiselect-checkbox" @checked($isSelected)>
                                        <span class="option-text">{{ $supplier->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_ctp') }}</label>
                    <select name="ctp_supplier_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(request('ctp_supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_color_count') }}</label>
                    <select name="color_count" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @for($c = 1; $c <= 10; $c++)
                            <option value="{{ $c }}" @selected(request('color_count') == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_paper_material') }}</label>
                    <select name="paper_type_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($paperTypes as $paperType)
                            <option value="{{ $paperType->id }}" @selected(request('paper_type_id') == $paperType->id)>{{ $paperType->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_job_size') }}</label>
                    <input type="number" step="0.01" min="0" name="job_size" class="form-control form-control-sm" value="{{ request('job_size') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_pull_count') }}</label>
                    <input type="number" min="0" step="1" name="pull_count" class="form-control form-control-sm" value="{{ request('pull_count') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_quantity_per_sheet') }}</label>
                    <input type="number" min="0" step="1" name="quantity_per_sheet" class="form-control form-control-sm" value="{{ request('quantity_per_sheet') }}">
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_service_1') }}</label>
                    <select name="service_1_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" @selected(request('service_1_id') == $service->id)>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field">
                    <label class="form-label">{{ __('dobs.operation_status') }}</label>
                    <select name="operation_status_id" class="form-control form-control-sm">
                        <option value="">{{ __('dobs.filter_all') }}</option>
                        @foreach($operationStatuses as $status)
                            <option value="{{ $status->id }}" @selected(request('operation_status_id') == $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group report-filter-field report-filter-field-wide">
                    <label class="form-label">{{ __('dobs.operation_notes') }}</label>
                    <input type="text" name="notes" class="form-control form-control-sm" value="{{ request('notes') }}" placeholder="{{ __('dobs.report_search_in_notes') }}">
                </div>
            @endif
        </div>
    </details>
</form>

@section('styles')
    @parent
    <style>
        .custom-multiselect-container {
            position: relative;
            width: 100%;
        }

        .custom-multiselect-trigger {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            gap: 0.5rem;
            padding-right: 0.5rem !important;
            padding-left: 0.5rem !important;
        }

        .custom-multiselect-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-grow: 1;
            text-align: right;
        }

        .custom-multiselect-chevron {
            font-size: 0.7rem;
            transition: transform 0.2s ease;
            flex-shrink: 0;
            opacity: 0.7;
        }

        .custom-multiselect-container.is-open .custom-multiselect-chevron {
            transform: rotate(180deg);
        }

        .custom-multiselect-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 100%;
            width: max-content;
            max-width: 350px;
            z-index: 9999;
            background-color: var(--bs-body-bg, #fff);
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            margin-top: 4px;
            padding: 0.5rem;
        }

        html[data-theme="dark"] .custom-multiselect-dropdown,
        body.dark-mode .custom-multiselect-dropdown,
        .dark .custom-multiselect-dropdown {
            background-color: #1e1e2d;
            border-color: #323248;
            color: #fff;
        }

        .custom-multiselect-container.is-open .custom-multiselect-dropdown {
            display: block;
        }

        .custom-multiselect-search-box {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .custom-multiselect-search-box i {
            position: absolute;
            top: 50%;
            right: 0.6rem;
            transform: translateY(-50%);
            opacity: 0.5;
            font-size: 0.8rem;
            pointer-events: none;
        }

        .custom-multiselect-search-input {
            width: 100%;
            padding: 0.3rem 1.8rem 0.3rem 0.6rem;
            font-size: 0.85rem;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 0.25rem;
            background-color: transparent;
            color: inherit;
        }

        html[data-theme="dark"] .custom-multiselect-search-input,
        body.dark-mode .custom-multiselect-search-input,
        .dark .custom-multiselect-search-input {
            border-color: #323248;
            color: #fff;
        }

        .custom-multiselect-search-input:focus {
            outline: none;
            border-color: var(--bs-primary, #0d6efd);
        }

        .custom-multiselect-actions {
            display: flex;
            justify-content: space-between;
            gap: 0.4rem;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--bs-border-color, #eee);
        }

        html[data-theme="dark"] .custom-multiselect-actions,
        body.dark-mode .custom-multiselect-actions,
        .dark .custom-multiselect-actions {
            border-color: #323248;
        }

        .custom-multiselect-actions button {
            background: none;
            border: none;
            color: var(--bs-primary, #0d6efd);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
        }

        .custom-multiselect-actions button:hover {
            background-color: rgba(13, 110, 253, 0.1);
            text-decoration: none;
        }

        .custom-multiselect-options {
            overflow-y: auto;
            max-height: 200px;
            overflow-x: hidden;
            display: block;
        }

        .custom-multiselect-option-label {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.4rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            user-select: none;
            transition: background-color 0.15s ease;
            text-align: right;
            margin-bottom: 0.15rem;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal !important;
        }

        .custom-multiselect-option-label .option-text {
            flex: 1;
            min-width: 0;
            width: 100%;
            display: block;
            white-space: normal !important;
        }



        .custom-multiselect-option-label:hover {
            background-color: rgba(0,0,0,0.05);
        }

        html[data-theme="dark"] .custom-multiselect-option-label:hover,
        body.dark-mode .custom-multiselect-option-label:hover,
        .dark .custom-multiselect-option-label:hover {
            background-color: rgba(255,255,255,0.05);
        }

        .custom-multiselect-checkbox {
            width: 15px;
            height: 15px;
            cursor: pointer;
            margin: 0;
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .custom-multiselect-option-label.is-hidden {
            display: none !important;
        }
    </style>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const multiselects = document.querySelectorAll('.custom-multiselect-container');
            
            multiselects.forEach(container => {
                const trigger = container.querySelector('.custom-multiselect-trigger');
                const dropdown = container.querySelector('.custom-multiselect-dropdown');
                const searchInput = container.querySelector('.custom-multiselect-search-input');
                const optionsContainer = container.querySelector('.custom-multiselect-options');
                const checkboxes = container.querySelectorAll('.custom-multiselect-checkbox');
                const selectAllCheckbox = container.querySelector('.custom-multiselect-select-all-checkbox');
                const labelEl = container.querySelector('.custom-multiselect-label');
                
                // Update select all checkbox state
                const updateSelectAllState = () => {
                    if (!selectAllCheckbox) return;
                    const visibleLabels = Array.from(optionsContainer.querySelectorAll('.custom-multiselect-option-label:not(.is-hidden):not(.select-all-label)'));
                    if (visibleLabels.length === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                        return;
                    }
                    
                    const checkedCount = visibleLabels.filter(label => {
                        const cb = label.querySelector('.custom-multiselect-checkbox');
                        return cb && cb.checked;
                    }).length;
                    
                    if (checkedCount === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedCount === visibleLabels.length) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                };

                // Toggle dropdown
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    
                    // Close other custom multiselects first
                    multiselects.forEach(other => {
                        if (other !== container) {
                            other.classList.remove('is-open');
                        }
                    });
                    
                    container.classList.toggle('is-open');
                    if (container.classList.contains('is-open')) {
                        searchInput.focus();
                    }
                });
                
                // Keyboard trigger access
                trigger.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                        e.preventDefault();
                        trigger.click();
                    }
                });
                
                // Prevent closing dropdown when clicking inside it
                dropdown.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    container.classList.remove('is-open');
                });
                
                // Search filter
                searchInput.addEventListener('input', () => {
                    const query = searchInput.value.toLowerCase().trim();
                    const optionLabels = optionsContainer.querySelectorAll('.custom-multiselect-option-label');
                    
                    optionLabels.forEach(label => {
                        const name = label.dataset.searchName || '';
                        if (name.includes(query)) {
                            label.classList.remove('is-hidden');
                        } else {
                            label.classList.add('is-hidden');
                        }
                    });
                    
                    updateSelectAllState();
                });
                
                // Update label
                const updateLabel = () => {
                    const checkedLabels = [];
                    checkboxes.forEach(cb => {
                        if (cb.checked) {
                            const text = cb.nextElementSibling.textContent.trim();
                            checkedLabels.push(text);
                        }
                    });
                    
                    if (checkedLabels.length === 0) {
                        labelEl.textContent = labelEl.dataset.defaultText || 'الكل';
                    } else if (checkedLabels.length <= 2) {
                        labelEl.textContent = checkedLabels.join('، ');
                    } else {
                        labelEl.textContent = `${checkedLabels.slice(0, 2).join('، ')} (+${checkedLabels.length - 2})`;
                    }
                };
                
                // Initial update
                updateLabel();
                updateSelectAllState();
                
                // Checkbox change
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', () => {
                        updateLabel();
                        updateSelectAllState();
                    });
                });
                
                // Select All Checkbox change
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', (e) => {
                        const isChecked = e.target.checked;
                        const optionLabels = optionsContainer.querySelectorAll('.custom-multiselect-option-label:not(.is-hidden):not(.select-all-label)');
                        optionLabels.forEach(label => {
                            const cb = label.querySelector('.custom-multiselect-checkbox');
                            if (cb) cb.checked = isChecked;
                        });
                        updateLabel();
                        updateSelectAllState(); // Resync state visually
                    });
                }
            });
        });
    </script>
@endsection
