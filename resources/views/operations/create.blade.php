@extends('layouts.app')

@section('title', __('dobs.create_operation'))

@section('header_title', __('dobs.create_operation'))
@section('header_subtitle', __('dobs.create_operation_subtitle'))

@section('header_actions')
<a href="{{ route('operations.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css">
@endsection

@section('content')
<div class="glass-card operation-form-card">
    <form
        action="{{ route('operations.store') }}"
        method="POST"
        id="operation-form"
        data-select-search="{{ __('dobs.select_search_placeholder') }}"
        data-select-no-results="{{ __('dobs.select_no_results') }}"
        data-option-create-url="{{ route('operations.form-options.store') }}"
        data-option-create-label="{{ __('dobs.select_create_option') }}"
        data-option-create-failed="{{ __('dobs.select_create_failed') }}"
    >
        @csrf

        @include('operations._form')

        <div class="form-actions">
            <a href="{{ route('operations.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_operation') }}
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script src="{{ asset('js/operation-form.js') }}?v={{ @filemtime(public_path('js/operation-form.js')) ?: 1 }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (@json(!isset($operation))) {
            const pad = (n) => String(n).padStart(2, '0');
            const now = new Date();
            const timeStr = pad(now.getHours()) + ':' + pad(now.getMinutes());
            const display = document.getElementById('operation_time_display');
            const hidden = document.getElementById('operation_time');
            if (display && hidden) {
                display.textContent = timeStr;
                hidden.value = timeStr;
            }
        }
    });
</script>
@endsection
