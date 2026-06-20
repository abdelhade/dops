@extends('layouts.guest')

@section('title', __('dobs.reset_password_title'))

@section('content')
<div class="glass-card login-card">
    <div class="login-brand">
        <div class="brand-icon"><i class="fa-solid fa-key"></i></div>
        <h1>{{ __('dobs.reset_password_title') }}</h1>
        <p>{{ __('dobs.reset_password_subtitle') }}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST" class="login-form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label for="email" class="form-label">{{ __('dobs.email') }}</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $email ?? old('email') }}" required autofocus readonly>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">{{ __('dobs.new_password') }}</label>
            <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password">
        </div>

        <div class="form-group">
            <label for="password-confirm" class="form-label">{{ __('dobs.password_confirmation') }}</label>
            <input type="password" name="password_confirmation" id="password-confirm" class="form-control" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-save"></i> {{ __('dobs.reset_password_button') }}
        </button>
    </form>
</div>
@endsection
