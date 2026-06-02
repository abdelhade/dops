@extends('layouts.guest')

@section('title', __('dobs.login_title'))

@section('content')
<div class="glass-card login-card">
    <div class="login-brand">
        <div class="brand-icon">D</div>
        <h1>{{ __('dobs.app_name') }}</h1>
        <p>{{ __('dobs.login_subtitle') }}</p>
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

    <form action="{{ route('login') }}" method="POST" class="login-form">
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">{{ __('dobs.email') }}</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">{{ __('dobs.password') }}</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <label class="remember-row">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span>{{ __('dobs.remember_me') }}</span>
        </label>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-right-to-bracket"></i> {{ __('dobs.login') }}
        </button>
    </form>
</div>
@endsection
