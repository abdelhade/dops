@extends('layouts.app')

@section('title', __('dobs.create_item'))

@section('header_title', __('dobs.create_item'))
@section('header_subtitle', __('dobs.create_item_subtitle'))

@section('header_actions')
<a href="{{ route('items.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
@include('partials.spreadsheet-import', [
    'templateRoute' => route('items.template'),
    'importRoute' => route('items.import'),
    'maxWidth' => '850px',
])
<div class="glass-card" style="max-width: 850px; margin: 0 auto;">
    <form action="{{ route('items.store') }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="name" class="form-label">{{ __('dobs.item_name') }} <span style="color: var(--color-danger)">{{ __('dobs.required_mark') }}</span></label>
                <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.item_name_placeholder') }}" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="sku" class="form-label">{{ __('dobs.sku') }}</label>
                <input type="text" name="sku" id="sku" class="form-control" placeholder="{{ __('dobs.sku_placeholder_auto') }}" value="{{ old('sku') }}">
            </div>
        </div>

        <div class="form-row" style="grid-template-columns: repeat(3, 1fr);">
            <div class="form-group">
                <label for="category_id" class="form-label">{{ __('dobs.item_category') }}</label>
                <select name="category_id" id="category_id" class="form-control">
                    <option value="">{{ __('dobs.select_category') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="supplier_id" class="form-label">{{ __('dobs.col_supplier') }}</label>
                <select name="supplier_id" id="supplier_id" class="form-control">
                    <option value="">{{ __('dobs.select_supplier') }}</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="paper_size_id" class="form-label">{{ __('dobs.col_paper_size') }}</label>
                <select name="paper_size_id" id="paper_size_id" class="form-control">
                    <option value="">{{ __('dobs.select_paper_size') }}</option>
                    @foreach($paperSizes as $ps)
                        <option value="{{ $ps->id }}" {{ old('paper_size_id') == $ps->id ? 'selected' : '' }}>
                            {{ $ps->name }}@if($ps->width) ({{ __('dobs.paper_size_dimensions', ['width' => $ps->width, 'height' => $ps->height, 'unit' => __('dobs.mm_unit')]) }})@endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price" class="form-label">{{ __('dobs.unit_price') }} ({{ __('dobs.currency') }}) <span style="color: var(--color-danger)">{{ __('dobs.required_mark') }}</span></label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="{{ __('dobs.zero_decimal') }}" value="{{ old('price', '0.00') }}" required>
            </div>

            <div class="form-group">
                <label for="stock" class="form-label">{{ __('dobs.initial_stock') }} <span style="color: var(--color-danger)">{{ __('dobs.required_mark') }}</span></label>
                <input type="number" name="stock" id="stock" class="form-control" placeholder="0" value="{{ old('stock', '0') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">{{ __('dobs.item_description') }}</label>
            <textarea name="description" id="description" class="form-control" placeholder="{{ __('dobs.item_description_placeholder') }}">{{ old('description') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('items.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_item') }}
            </button>
        </div>
    </form>
</div>
@endsection
