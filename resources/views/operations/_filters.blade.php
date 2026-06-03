<form method="GET" action="{{ route('operations.index') }}" class="filters-form">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.col_op_number') }}</label>
            <input type="text" name="operation_number" class="form-control form-control-sm" value="{{ request('operation_number') }}" placeholder="بحث بالرقم...">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">من تاريخ</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">إلى تاريخ</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.col_item') }}</label>
            <select name="item_id" class="form-control form-control-sm">
                <option value="">-- الكل --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" @selected(request('item_id') == $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.col_status') }}</label>
            <select name="operation_status_id" class="form-control form-control-sm">
                <option value="">-- الكل --</option>
                @foreach($operationStatuses as $status)
                    <option value="{{ $status->id }}" @selected(request('operation_status_id') == $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.operation_printing_press') }}</label>
            <select name="printing_supplier_id" class="form-control form-control-sm">
                <option value="">-- الكل --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(request('printing_supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.operation_ctp') }}</label>
            <select name="ctp_supplier_id" class="form-control form-control-sm">
                <option value="">-- الكل --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(request('ctp_supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.operation_paper_material') }}</label>
            <select name="material_id" class="form-control form-control-sm">
                <option value="">-- الكل --</option>
                @foreach($materials as $material)
                    <option value="{{ $material->id }}" @selected(request('material_id') == $material->id)>{{ $material->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">{{ __('dobs.operation_color_count') }}</label>
            <select name="color_count" class="form-control form-control-sm">
                <option value="">-- الكل --</option>
                @for($c=1; $c<=10; $c++)
                    <option value="{{ $c }}" @selected(request('color_count') == $c)>{{ $c }}</option>
                @endfor
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">الخدمات</label>
            <select name="service_id" class="form-control form-control-sm">
                <option value="">-- الكل --</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.85rem;">البيان/الملاحظات</label>
            <input type="text" name="statement" class="form-control form-control-sm" value="{{ request('statement') }}" placeholder="بحث بكلمة...">
        </div>

        <div class="form-group" style="margin-bottom: 0; display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;"><i class="fa-solid fa-filter"></i> فلترة</button>
            <a href="{{ route('operations.index') }}" class="btn btn-secondary btn-sm" title="مسح الفلاتر"><i class="fa-solid fa-xmark"></i></a>
        </div>

    </div>
</form>
