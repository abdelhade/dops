@extends('layouts.app')

@section('title', __('dobs.nav_materials'))

@section('header_title', __('dobs.materials_title'))
@section('header_subtitle', __('dobs.materials_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('materials.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_material') }}
        </a>
    @endif
@endsection

@section('content')
<div class="glass-card">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 5%">{{ __('dobs.col_id') }}</th>
                    <th style="width: 25%">{{ __('dobs.material_name') }}</th>
                    <th style="width: 15%">{{ __('dobs.material_code') }}</th>
                    <th style="width: 15%">{{ __('dobs.unit') }}</th>
                    <th style="width: 15%">{{ __('dobs.col_price') }}</th>
                    <th style="width: 10%">{{ __('dobs.col_stock') }}</th>
                    <th style="width: 15; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $material)
                    <tr>
                        <td>{{ $material->id }}</td>
                        <td>
                            <a href="{{ route('materials.show', $material->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $material->name }}
                            </a>
                        </td>
                        <td><code style="color: var(--color-secondary);">{{ $material->code ?? __('dobs.na') }}</code></td>
                        <td>{{ $material->unit }}</td>
                        <td style="font-weight: 700; color: white;">{{ number_format($material->price, 2) }} {{ __('dobs.currency') }}</td>
                        <td>{{ $material->stock }}</td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('materials.show', $material->id),
                                'editRoute' => route('materials.edit', $material->id),
                                'destroyRoute' => route('materials.destroy', $material->id),
                                'confirmMessage' => __('dobs.confirm_delete_material'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fa-solid fa-pallet"></i>
                            {{ __('dobs.no_materials') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
