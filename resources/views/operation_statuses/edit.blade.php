@extends('layouts.app')

@section('title', __('dobs.edit_status'))

@section('header_title', __('dobs.edit_status'))
@section('header_subtitle', __('dobs.edit_status_subtitle'))

@section('header_actions')
    <a href="{{ route('operation-statuses.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
    </a>
@endsection

@section('content')
<div class="glass-card">
    <form action="{{ route('operation-statuses.update', $operationStatus->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            <div class="form-group">
                <label class="form-label">{{ __('dobs.status_name') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $operationStatus->name) }}" placeholder="{{ __('dobs.status_name_placeholder') }}" required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('dobs.sort_order') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $operationStatus->sort_order) }}" required>
                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('dobs.status_days') }} <span class="text-danger">{{ __('dobs.required_mark') }}</span></label>
                <input type="number" name="days" min="1" step="1" class="form-control @error('days') is-invalid @enderror" value="{{ old('days', $operationStatus->days ?? 1) }}" required>
                @error('days')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('dobs.status_color') }}</label>
                <input type="color" name="color" class="form-control @error('color') is-invalid @enderror" value="{{ old('color', $operationStatus->color) }}">
                @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('dobs.status_is_end') }}</label>
                <div style="display: flex; align-items: center; gap: 0.5rem; min-height: 42px;">
                    <input type="checkbox" name="is_end" value="1" id="is_end" {{ old('is_end', $operationStatus->is_end) ? 'checked' : '' }}>
                    <label for="is_end" style="margin: 0; cursor: pointer;">{{ __('dobs.status_is_end_hint') }}</label>
                </div>
                @error('is_end')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">{{ __('dobs.description') }}</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('description', $operationStatus->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_status') }}
            </button>
        </div>
    </form>
</div>
@endsection
