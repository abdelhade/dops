@extends('layouts.app')

@section('title', __('dobs.create_material'))

@section('header_title', __('dobs.create_material'))
@section('header_subtitle', __('dobs.create_material_subtitle'))

@section('header_actions')
<a href="{{ route('materials.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
@include('partials.spreadsheet-import', [
    'templateRoute' => route('materials.template'),
    'importRoute' => route('materials.import'),
])
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('materials.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.material_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.material_name_placeholder') }}" value="{{ old('name') }}" required>
        </div>



        <div class="form-group">
            <label for="description" class="form-label">{{ __('dobs.description') }}</label>
            <textarea name="description" id="description" class="form-control" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('description') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('materials.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_material') }}
            </button>
        </div>
    </form>
</div>
@endsection
