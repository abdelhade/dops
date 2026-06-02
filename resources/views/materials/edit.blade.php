@extends('layouts.app')

@section('title', __('dobs.edit_material'))

@section('header_title', __('dobs.edit_material'))
@section('header_subtitle', __('dobs.edit_material_subtitle'))

@section('header_actions')
<a href="{{ route('materials.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('materials.update', $material->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.material_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.material_name_placeholder') }}" value="{{ old('name', $material->name) }}" required>
        </div>

        <div class="form-group">
            <label for="code" class="form-label">{{ __('dobs.material_code') }}</label>
            <input type="text" name="code" id="code" class="form-control" placeholder="{{ __('dobs.material_code_placeholder') }}" value="{{ old('code', $material->code) }}">
        </div>

        <div class="form-group">
            <label for="unit" class="form-label">{{ __('dobs.unit') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="unit" id="unit" class="form-control" placeholder="{{ __('dobs.unit_placeholder') }}" value="{{ old('unit', $material->unit) }}" required>
        </div>

        <div class="form-group">
            <label for="price" class="form-label">{{ __('dobs.price_per_unit') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="{{ __('dobs.zero_decimal') }}" value="{{ old('price', $material->price) }}" required>
        </div>

        <div class="form-group">
            <label for="stock" class="form-label">{{ __('dobs.stock_level') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="number" name="stock" id="stock" class="form-control" placeholder="{{ __('dobs.qty_placeholder') }}" value="{{ old('stock', $material->stock) }}" required>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">{{ __('dobs.description') }}</label>
            <textarea name="description" id="description" class="form-control" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('description', $material->description) }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('materials.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_changes') }}
            </button>
        </div>
    </form>
</div>
@endsection
