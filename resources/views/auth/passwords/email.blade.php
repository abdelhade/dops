@extends('layouts.guest')

@section('title', __('dobs.forgot_password_title'))

@section('content')
<div class="glass-card login-card">
    <div class="login-brand">
        <div class="brand-icon"><i class="fa-solid fa-unlock-keyhole"></i></div>
        <h1>{{ __('dobs.forgot_password_title') }}</h1>
        <p>{{ __('dobs.forgot_password_subtitle') }}</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <div>
                {{ session('status') }}
            </div>
        </div>
    @endif

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

    <form action="{{ route('password.email') }}" method="POST" class="login-form">
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">{{ __('dobs.email') }}</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-paper-plane"></i> {{ __('dobs.send_password_reset_link') }}
        </button>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="{{ route('login') }}" class="text-muted" style="text-decoration: none;">
                <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_login') }}
            </a>
        </div>
    </form>
</div>
@endsection
