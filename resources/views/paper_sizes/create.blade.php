@extends('layouts.app')

@section('title', __('dobs.create_paper_size'))

@section('header_title', __('dobs.create_paper_size'))
@section('header_subtitle', __('dobs.create_paper_size_subtitle'))

@section('header_actions')
<a href="{{ route('paper-sizes.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('paper-sizes.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.size_name') }} <span style="color: var(--color-danger)">{{ __('dobs.required_mark') }}</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.size_name_placeholder') }}" value="{{ old('name') }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="width" class="form-label">{{ __('dobs.width_mm') }}</label>
                <input type="number" step="0.01" name="width" id="width" class="form-control" placeholder="{{ __('dobs.width_placeholder') }}" value="{{ old('width') }}">
            </div>

            <div class="form-group">
                <label for="height" class="form-label">{{ __('dobs.height_mm') }}</label>
                <input type="number" step="0.01" name="height" id="height" class="form-control" placeholder="{{ __('dobs.height_placeholder') }}" value="{{ old('height') }}">
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('paper-sizes.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_paper_size') }}
            </button>
        </div>
    </form>
</div>
@endsection
