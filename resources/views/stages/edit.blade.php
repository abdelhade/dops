@extends('layouts.app')

@section('title', __('dobs.edit_stage'))

@section('header_title', __('dobs.edit_stage'))
@section('header_subtitle', __('dobs.edit_stage_subtitle'))

@section('header_actions')
<a href="{{ route('stages.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('stages.update', $stage->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.stage_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.stage_name_placeholder') }}" value="{{ old('name', $stage->name) }}" required>
        </div>

        <div class="form-group">
            <label for="sort_order" class="form-label">{{ __('dobs.sort_order') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="number" name="sort_order" id="sort_order" class="form-control" placeholder="{{ __('dobs.sort_order_placeholder') }}" value="{{ old('sort_order', $stage->sort_order) }}" required>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">{{ __('dobs.description') }}</label>
            <textarea name="description" id="description" class="form-control" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('description', $stage->description) }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('stages.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_changes') }}
            </button>
        </div>
    </form>
</div>
@endsection
