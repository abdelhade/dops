@php
    $filterAction = $filterAction ?? route('reports.paper-materials-summary');
    $clearFiltersUrl = $clearFiltersUrl ?? route('reports.paper-materials-summary');
    $defaultDateFrom = request('date_from', now()->startOfMonth()->format('Y-m-d'));
    $defaultDateTo = request('date_to', now()->format('Y-m-d'));
@endphp

<form method="GET" action="{{ $filterAction }}" class="report-filters-form">
    <input type="hidden" name="applied" value="1">

    <div class="report-filters-advanced-grid">
        <div class="form-group report-filter-field">
            <label class="form-label">{{ __('dobs.operation_serial') }}</label>
            <input type="text" name="operation_number" class="form-control form-control-sm" value="{{ request('operation_number') }}" placeholder="{{ __('dobs.report_search_by_serial') }}">
        </div>

        <div class="form-group report-filter-field">
            <label class="form-label">{{ __('dobs.date_from') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $defaultDateFrom }}" required>
        </div>

        <div class="form-group report-filter-field">
            <label class="form-label">{{ __('dobs.date_to') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $defaultDateTo }}" required>
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
            <label class="form-label">{{ __('dobs.operation_service_2') }}</label>
            <select name="service_2_id" class="form-control form-control-sm">
                <option value="">{{ __('dobs.filter_all') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" @selected(request('service_2_id') == $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group report-filter-field">
            <label class="form-label">{{ __('dobs.operation_service_3') }}</label>
            <select name="service_3_id" class="form-control form-control-sm">
                <option value="">{{ __('dobs.filter_all') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" @selected(request('service_3_id') == $service->id)>{{ $service->name }}</option>
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

        <div class="form-group report-filter-field">
            <label class="form-label">{{ __('dobs.operation_notes') }}</label>
            <input type="text" name="notes" class="form-control form-control-sm" value="{{ request('notes') }}" placeholder="{{ __('dobs.report_search_in_notes') }}">
        </div>
    </div>

    <div class="form-group report-filter-field report-filter-field-wide" style="margin-top: 0.85rem; margin-bottom: 0;">
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

    <div class="report-filters-actions" style="margin-top: 0.85rem;">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter"></i> {{ __('dobs.report_show_results') }}
        </button>
        <a href="{{ $clearFiltersUrl }}" class="btn btn-secondary" title="{{ __('dobs.clear_filters') }}">
            <i class="fa-solid fa-rotate-left"></i> {{ __('dobs.clear_filters') }}
        </a>
    </div>
</form>
