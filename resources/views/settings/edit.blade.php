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

            <section class="settings-section" style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 2rem;">
                <h2 class="settings-section-title">
                    <i class="fa-solid fa-envelope"></i>
                    {{ __('dobs.settings_mail_section') }}
                </h2>
                <p class="settings-section-hint">{{ __('dobs.settings_mail_hint') }}</p>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="mail_host" class="form-label">{{ __('dobs.settings_mail_host') }}</label>
                        <input type="text" name="mail_host" id="mail_host" class="form-control" value="{{ old('mail_host', $mailHost) }}">
                    </div>

                    <div class="form-group">
                        <label for="mail_port" class="form-label">{{ __('dobs.settings_mail_port') }}</label>
                        <input type="number" name="mail_port" id="mail_port" class="form-control" value="{{ old('mail_port', $mailPort) }}">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="mail_username" class="form-label">{{ __('dobs.settings_mail_username') }}</label>
                        <input type="text" name="mail_username" id="mail_username" class="form-control" value="{{ old('mail_username', $mailUsername) }}" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="mail_password" class="form-label">{{ __('dobs.settings_mail_password') }}</label>
                        <input type="password" name="mail_password" id="mail_password" class="form-control" value="{{ old('mail_password', $mailPassword) }}" autocomplete="new-password">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="mail_encryption" class="form-label">{{ __('dobs.settings_mail_encryption') }}</label>
                        <input type="text" name="mail_encryption" id="mail_encryption" class="form-control" value="{{ old('mail_encryption', $mailEncryption) }}" placeholder="tls / ssl">
                    </div>

                    <div class="form-group">
                        <label for="mail_from_address" class="form-label">{{ __('dobs.settings_mail_from_address') }}</label>
                        <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" value="{{ old('mail_from_address', $mailFromAddress) }}">
                    </div>

                    <div class="form-group">
                        <label for="mail_from_name" class="form-label">{{ __('dobs.settings_mail_from_name') }}</label>
                        <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" value="{{ old('mail_from_name', $mailFromName) }}" placeholder="{{ __('dobs.app_name') }}">
                    </div>
                </div>
            </section>

            <section class="settings-section" style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 2rem;">
                <h2 class="settings-section-title">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    {{ __('dobs.settings_daftara_section') }}
                </h2>
                <p class="settings-section-hint">{{ __('dobs.settings_daftara_hint') }}</p>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="daftara_subdomain" class="form-label">{{ __('dobs.settings_daftara_subdomain') }}</label>
                        <input type="text" name="daftara_subdomain" id="daftara_subdomain" class="form-control" value="{{ old('daftara_subdomain', $daftaraSubdomain) }}" placeholder="e.g. company">
                    </div>

                    <div class="form-group">
                        <label for="daftara_api_key" class="form-label">{{ __('dobs.settings_daftara_api_key') }}</label>
                        <input type="password" name="daftara_api_key" id="daftara_api_key" class="form-control" value="{{ old('daftara_api_key', $daftaraApiKey) }}" autocomplete="off">
                    </div>
                </div>
            </section>

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
