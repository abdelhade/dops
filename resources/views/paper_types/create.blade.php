@extends('layouts.app')

@section('title', __('dobs.create_paper_type'))

@section('header_title', __('dobs.create_paper_type'))
@section('header_subtitle', __('dobs.create_paper_type_subtitle'))

@section('header_actions')
<a href="{{ route('paper-types.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
@include('partials.spreadsheet-import', [
    'templateRoute' => route('paper-types.template'),
    'importRoute' => route('paper-types.import'),
])
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('paper-types.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.paper_type_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.paper_type_name_placeholder') }}" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="weight_gsm" class="form-label">{{ __('dobs.weight_gsm') }}</label>
            <input type="number" name="weight_gsm" id="weight_gsm" class="form-control" placeholder="{{ __('dobs.weight_gsm_placeholder') }}" value="{{ old('weight_gsm') }}">
        </div>

        <div class="form-group">
            <label for="finish" class="form-label">{{ __('dobs.finish') }}</label>
            <input type="text" name="finish" id="finish" class="form-control" placeholder="{{ __('dobs.finish_placeholder') }}" value="{{ old('finish') }}">
        </div>

        <div class="form-group">
            <label for="description" class="form-label">{{ __('dobs.description') }}</label>
            <textarea name="description" id="description" class="form-control" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('description') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('paper-types.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_paper_type') }}
            </button>
        </div>
    </form>
</div>
@endsection
