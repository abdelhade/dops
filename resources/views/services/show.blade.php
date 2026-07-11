@extends('layouts.app')

@section('title', __('dobs.service_details'))

@section('header_title', $service->name)
@section('header_subtitle', __('dobs.service_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;">
    <a href="{{ route('services.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back') }}
    </a>
    @if (auth()->user()?->hasPermission('services', 'update'))
        <a href="{{ route('services.edit', $service->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> {{ __('dobs.edit') }}
        </a>
    @endif
</div>
@endsection

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-bell-concierge" style="color: var(--color-primary);"></i>
            {{ __('dobs.service_details') }}
        </h2>
        
        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
            <div>
                <span class="stat-label">{{ __('dobs.service_name') }}</span>
                <div style="font-size:1.15rem; font-weight:600; margin-top:0.25rem;">{{ $service->name }}</div>
            </div>
        </div>

        <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <span class="stat-label">{{ __('dobs.col_description') }}</span>
            <div style="font-size:1rem; color:var(--text-secondary); margin-top:0.5rem; white-space:pre-line;">
                {{ $service->description ?: __('dobs.no_description_provided') }}
            </div>
        </div>
    </div>
</div>
@endsection
