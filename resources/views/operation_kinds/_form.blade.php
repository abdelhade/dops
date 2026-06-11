@php
    $kind = $operationKind ?? null;
@endphp

<div class="form-group">
    <label for="operation_kind_name" class="form-label">{{ __('dobs.operation_kind_name') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
    <input type="text" name="name" id="operation_kind_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $kind?->name) }}" required autofocus>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="operation_kind_sort_order" class="form-label">{{ __('dobs.sort_order') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
    <input type="number" name="sort_order" id="operation_kind_sort_order" min="0" step="1" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $kind?->sort_order ?? 0) }}" required>
    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="operation_kind_description" class="form-label">{{ __('dobs.description') }}</label>
    <textarea name="description" id="operation_kind_description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $kind?->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
