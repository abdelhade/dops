@php
    use App\Enums\OperationTypeMode;

    $type = $operationType ?? null;
    $isSystem = (bool) ($type?->is_system);
@endphp

<div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
    <div class="form-group">
        <label class="form-label" for="operation_type_name">{{ __('dobs.operation_type_name') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
        <input type="text" name="name" id="operation_type_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $type?->name) }}" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="operation_type_slug">{{ __('dobs.operation_type_slug') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
        <input
            type="text"
            name="slug"
            id="operation_type_slug"
            class="form-control form-control-mono @error('slug') is-invalid @enderror"
            value="{{ old('slug', $type?->slug) }}"
            pattern="[a-z0-9_]+"
            {{ $isSystem ? 'readonly' : 'required' }}
        >
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="operation_type_form_mode">{{ __('dobs.operation_type_form_mode') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
        <select name="form_mode" id="operation_type_form_mode" class="form-control @error('form_mode') is-invalid @enderror" {{ $isSystem ? 'disabled' : 'required' }}>
            @foreach(OperationTypeMode::casesForSelect() as $mode)
                <option value="{{ $mode->value }}" @selected(old('form_mode', $type?->form_mode?->value) === $mode->value)>
                    {{ $mode->label() }}
                </option>
            @endforeach
        </select>
        @if($isSystem)
            <input type="hidden" name="form_mode" value="{{ $type?->form_mode?->value }}">
        @endif
        @error('form_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="operation_type_serial_prefix">{{ __('dobs.operation_type_serial_prefix') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
        <input type="text" name="serial_prefix" id="operation_type_serial_prefix" class="form-control form-control-mono @error('serial_prefix') is-invalid @enderror" value="{{ old('serial_prefix', $type?->serial_prefix) }}" required>
        @error('serial_prefix')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="operation_type_sort_order">{{ __('dobs.sort_order') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
        <input type="number" name="sort_order" id="operation_type_sort_order" min="0" step="1" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $type?->sort_order ?? 0) }}" required>
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label class="form-label" for="operation_type_description">{{ __('dobs.description') }}</label>
        <textarea name="description" id="operation_type_description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $type?->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
