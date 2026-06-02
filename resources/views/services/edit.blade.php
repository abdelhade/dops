@extends('layouts.app')

@section('title', __('dobs.edit_service'))

@section('header_title', __('dobs.edit_service'))
@section('header_subtitle', __('dobs.edit_service_subtitle'))

@section('header_actions')
<a href="{{ route('services.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('services.update', $service->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.service_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.service_name_placeholder') }}" value="{{ old('name', $service->name) }}" required>
        </div>

        <div class="form-group">
            <label for="price" class="form-label">{{ __('dobs.col_price') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="{{ __('dobs.zero_decimal') }}" value="{{ old('price', $service->price) }}" required>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">{{ __('dobs.description') }}</label>
            <textarea name="description" id="description" class="form-control" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('description', $service->description) }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('services.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_changes') }}
            </button>
        </div>
    </form>
</div>
@endsection
