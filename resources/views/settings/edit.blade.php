@extends('layouts.app')

@section('title', __('dobs.settings_title'))

@section('header_title', __('dobs.settings_title'))
@section('header_subtitle', __('dobs.settings_subtitle'))

@section('header_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_dashboard') }}
    </a>
@endsection

@section('content')
<div class="glass-card settings-card">
    <section class="settings-section">
        <h2 class="settings-section-title">
            <i class="fa-solid fa-palette"></i>
            {{ __('dobs.settings_theme_section') }}
        </h2>
        <p class="settings-section-hint">{{ __('dobs.settings_theme_hint') }}</p>

        <div class="settings-theme-control">
            <button
                type="button"
                class="theme-toggle settings-theme-toggle"
                id="themeToggle"
                data-label-monokai="{{ __('dobs.toggle_theme_monokai') }}"
                data-label-dark="{{ __('dobs.toggle_theme_dark') }}"
                aria-label="{{ __('dobs.toggle_theme_dark') }}"
            >
                <i class="fa-solid fa-sun theme-icon-dark" aria-hidden="true" hidden></i>
                <i class="fa-solid fa-moon theme-icon-monokai" aria-hidden="true"></i>
            </button>
            <span id="settingsThemeLabel" class="settings-theme-label"></span>
        </div>
    </section>

    <section class="settings-section">
        <h2 class="settings-section-title">
            <i class="fa-solid fa-lock"></i>
            {{ __('dobs.settings_delete_password_section') }}
        </h2>
        <p class="settings-section-hint">{{ __('dobs.settings_delete_password_hint') }}</p>

        @if ($deletePasswordConfigured)
            <p class="settings-password-status">
                <i class="fa-solid fa-circle-check"></i>
                {{ __('dobs.settings_delete_password_configured') }}
            </p>
        @else
            <p class="settings-password-status settings-password-status--warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ __('dobs.settings_delete_password_missing') }}
            </p>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" class="settings-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="delete_password" class="form-label">{{ __('dobs.settings_delete_password') }}</label>
                <input
                    type="password"
                    name="delete_password"
                    id="delete_password"
                    class="form-control"
                    autocomplete="new-password"
                >
                <small class="form-hint">{{ __('dobs.settings_delete_password_blank_hint') }}</small>
            </div>

            <div class="form-group">
                <label for="delete_password_confirmation" class="form-label">{{ __('dobs.settings_delete_password_confirmation') }}</label>
                <input
                    type="password"
                    name="delete_password_confirmation"
                    id="delete_password_confirmation"
                    class="form-control"
                    autocomplete="new-password"
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> {{ __('dobs.save_settings') }}
                </button>
            </div>
        </form>
    </section>
</div>
@endsection

@section('scripts')
<script>
    window.DOBS_SETTINGS_LANG = {
        themeMonokai: @json(__('dobs.settings_theme_current_monokai')),
        themeDark: @json(__('dobs.settings_theme_current_dark')),
    };
</script>
<script src="{{ asset('js/theme.js') }}?v={{ @filemtime(public_path('js/theme.js')) ?: 1 }}"></script>
<script src="{{ asset('js/settings.js') }}?v={{ @filemtime(public_path('js/settings.js')) ?: 1 }}"></script>
@endsection
