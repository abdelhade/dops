@extends('layouts.app')

@section('title', __('dobs.create_service'))

@section('header_title', __('dobs.create_service'))
@section('header_subtitle', __('dobs.create_service_subtitle'))

@section('header_actions')
<a href="{{ route('services.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('services.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.service_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.service_name_placeholder') }}" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="price" class="form-label">{{ __('dobs.col_price') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="{{ __('dobs.zero_decimal') }}" value="{{ old('price', '0.00') }}" required>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">{{ __('dobs.description') }}</label>
            <textarea name="description" id="description" class="form-control" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('description') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('services.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_service') }}
            </button>
        </div>
    </form>
</div>
@endsection
