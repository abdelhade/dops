@extends('layouts.app')

@section('title', __('dobs.edit_user'))

@section('header_title', __('dobs.edit_user'))
@section('header_subtitle', $user->name)

@section('header_actions')
<a href="{{ route('users.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
@include('partials.role-permissions-hint')

<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.col_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">{{ __('dobs.email') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
            <label for="role" class="form-label">{{ __('dobs.col_role') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="role" id="role" class="form-control" required>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                        {{ __('dobs.role_' . $role) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">{{ __('dobs.password') }}</label>
            <input type="password" name="password" id="password" class="form-control">
            <small style="color: var(--text-muted); display:block; margin-top:0.35rem;">{{ __('dobs.leave_password_blank') }}</small>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">{{ __('dobs.password_confirmation') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
        </div>

        <div class="form-actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.update_user') }}
            </button>
        </div>
    </form>
</div>
@endsection
