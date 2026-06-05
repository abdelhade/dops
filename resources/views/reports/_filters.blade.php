@php
    $filterAction = $filterAction ?? route('reports.paper-materials-summary');
    $clearFiltersUrl = $clearFiltersUrl ?? route('reports.paper-materials-summary');
    $defaultDateFrom = request('date_from', now()->startOfMonth()->format('Y-m-d'));
    $defaultDateTo = request('date_to', now()->format('Y-m-d'));
@endphp

<form method="GET" action="{{ $filterAction }}" class="report-filters-form">
    <input type="hidden" name="applied" value="1">

    <div class="report-filters-primary">
        <div class="report-filters-primary-fields">
            <div class="form-group report-filter-field report-filter-field-dates">
                <label class="form-label">{{ __('dobs.date_from') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ $defaultDateFrom }}" required>
            </div>

            <div class="form-group report-filter-field report-filter-field-dates">
                <label class="form-label">{{ __('dobs.date_to') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ $defaultDateTo }}" required>
            </div>

            <div class="form-group report-filter-field report-filter-field-search">
                <label class="form-label">{{ __('dobs.report_global_search') }}</label>
                <div class="report-filter-search-wrap">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
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
        </div>
    </div>

    <details class="report-filters-advanced" @if(request()->filled('operation_number') || request()->filled('item_id') || request()->filled('operation_status_id') || request()->filled('printing_supplier_id') || request()->filled('ctp_supplier_id') || request()->filled('paper_type_id') || request()->filled('color_count') || request()->filled('service_id') || request()->filled('statement')) open @endif>
        <summary class="report-filters-advanced-toggle">
            <i class="fa-solid fa-sliders"></i>
            <span>{{ __('dobs.report_advanced_filters') }}</span>
        </summary>

        <div class="report-filters-advanced-grid">
            <div class="form-group report-filter-field">
                <label class="form-label">{{ __('dobs.col_op_number') }}</label>
                <input type="text" name="operation_number" class="form-control form-control-sm" value="{{ request('operation_number') }}" placeholder="{{ __('dobs.report_search_by_serial') }}">
            </div>

            <div class="form-group report-filter-field">
                <label class="form-label">{{ __('dobs.col_item') }}</label>
                <select name="item_id" class="form-control form-control-sm">
                    <option value="">{{ __('dobs.filter_all') }}</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" @selected(request('item_id') == $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group report-filter-field">
                <label class="form-label">{{ __('dobs.col_status') }}</label>
                <select name="operation_status_id" class="form-control form-control-sm">
                    <option value="">{{ __('dobs.filter_all') }}</option>
                    @foreach($operationStatuses as $status)
                        <option value="{{ $status->id }}" @selected(request('operation_status_id') == $status->id)>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group report-filter-field">
                <label class="form-label">{{ __('dobs.operation_printing_press') }}</label>
                <select name="printing_supplier_id" class="form-control form-control-sm">
                    <option value="">{{ __('dobs.filter_all') }}</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('printing_supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
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
                <label class="form-label">{{ __('dobs.operation_paper_material') }}</label>
                <select name="paper_type_id" class="form-control form-control-sm">
                    <option value="">{{ __('dobs.filter_all') }}</option>
                    @foreach($paperTypes as $paperType)
                        <option value="{{ $paperType->id }}" @selected(request('paper_type_id') == $paperType->id)>{{ $paperType->name }}</option>
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
                <label class="form-label">{{ __('dobs.report_col_services') }}</label>
                <select name="service_id" class="form-control form-control-sm">
                    <option value="">{{ __('dobs.filter_all') }}</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group report-filter-field report-filter-field-wide">
                <label class="form-label">{{ __('dobs.report_col_notes') }}</label>
                <input type="text" name="statement" class="form-control form-control-sm" value="{{ request('statement') }}" placeholder="{{ __('dobs.report_search_in_notes') }}">
            </div>
        </div>
    </details>
</form>
