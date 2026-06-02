@extends('layouts.app')

@section('title', __('dobs.material_details'))

@section('header_title', $material->name)
@section('header_subtitle', __('dobs.material_details_subtitle'))

@section('header_actions')
<div style="display:flex; gap: 0.5rem;">
    <a href="{{ route('materials.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back') }}
    </a>
    @if (auth()->user()?->canEditRecords())
        <a href="{{ route('materials.edit', $material->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> {{ __('dobs.edit') }}
        </a>
    @endif
</div>
@endsection

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div class="glass-card">
        <h2 class="card-title" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-pallet" style="color: var(--color-primary);"></i>
            {{ __('dobs.material_details') }}
        </h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div>
                <span class="stat-label">{{ __('dobs.material_name') }}</span>
                <div style="font-size:1.15rem; font-weight:600; margin-top:0.25rem;">{{ $material->name }}</div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.material_code') }}</span>
                <div style="font-size:1.15rem; font-weight:600; margin-top:0.25rem;">
                    <code style="color: var(--color-secondary);">{{ $material->code ?? __('dobs.na') }}</code>
                </div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.unit') }}</span>
                <div style="font-size:1.15rem; font-weight:600; margin-top:0.25rem;">{{ $material->unit }}</div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.price_per_unit') }}</span>
                <div style="font-size:1.15rem; font-weight:600; margin-top:0.25rem; color: white;">
                    {{ number_format($material->price, 2) }} {{ __('dobs.currency') }}
                </div>
            </div>
            <div>
                <span class="stat-label">{{ __('dobs.col_stock') }}</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--color-success); margin-top:0.25rem;">
                    {{ $material->stock }}
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <span class="stat-label">{{ __('dobs.col_description') }}</span>
            <div style="font-size:1rem; color:var(--text-secondary); margin-top:0.5rem; white-space:pre-line;">
                {{ $material->description ?: __('dobs.no_description_provided') }}
            </div>
        </div>
    </div>
</div>
@endsection
