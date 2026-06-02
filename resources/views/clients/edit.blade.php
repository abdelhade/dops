@extends('layouts.app')

@section('title', __('dobs.edit_client'))

@section('header_title', __('dobs.edit_client'))
@section('header_subtitle', __('dobs.edit_client_subtitle'))

@section('header_actions')
<a href="{{ route('clients.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('clients.update', $client->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.client_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.client_name_placeholder') }}" value="{{ old('name', $client->name) }}" required>
        </div>

        <div class="form-group">
            <label for="phone" class="form-label">{{ __('dobs.phone') }}</label>
            <input type="text" name="phone" id="phone" class="form-control" placeholder="{{ __('dobs.phone_placeholder') }}" value="{{ old('phone', $client->phone) }}">
        </div>

        <div class="form-group">
            <label for="email" class="form-label">{{ __('dobs.email') }}</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="{{ __('dobs.email_placeholder') }}" value="{{ old('email', $client->email) }}">
        </div>

        <div class="form-group">
            <label for="address" class="form-label">{{ __('dobs.address') }}</label>
            <textarea name="address" id="address" class="form-control" placeholder="{{ __('dobs.address_placeholder') }}">{{ old('address', $client->address) }}</textarea>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">{{ __('dobs.col_notes_header') }}</label>
            <textarea name="notes" id="notes" class="form-control" placeholder="{{ __('dobs.description_placeholder') }}">{{ old('notes', $client->notes) }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_changes') }}
            </button>
        </div>
    </form>
</div>
@endsection
