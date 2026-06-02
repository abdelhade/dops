@extends('layouts.app')

@section('title', __('dobs.create_user'))

@section('header_title', __('dobs.create_user'))
@section('header_subtitle', __('dobs.create_user_subtitle'))

@section('header_actions')
<a href="{{ route('users.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.col_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">{{ __('dobs.email') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="role" class="form-label">{{ __('dobs.col_role') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="role" id="role" class="form-control" required>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                        {{ __('dobs.role_' . $role) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">{{ __('dobs.password') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">{{ __('dobs.password_confirmation') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>

        <div class="form-actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_user') }}
            </button>
        </div>
    </form>
</div>
@endsection
