@extends('layouts.app')

@section('title', __('dobs.edit_supplier'))

@section('header_title', __('dobs.edit_supplier'))
@section('header_subtitle', __('dobs.edit_supplier_subtitle'))

@section('header_actions')
<a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.supplier_name') }} <span style="color: var(--color-danger)">{{ __('dobs.required_mark') }}</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('dobs.supplier_name_placeholder') }}" value="{{ old('name', $supplier->name) }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email" class="form-label">{{ __('dobs.email') }}</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="{{ __('dobs.email_placeholder') }}" value="{{ old('email', $supplier->email) }}">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">{{ __('dobs.phone') }}</label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="{{ __('dobs.phone_placeholder') }}" value="{{ old('phone', $supplier->phone) }}">
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">{{ __('dobs.business_address') }}</label>
            <textarea name="address" id="address" class="form-control" placeholder="{{ __('dobs.business_address_placeholder') }}">{{ old('address', $supplier->address) }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.update_supplier') }}
            </button>
        </div>
    </form>
</div>
@endsection
