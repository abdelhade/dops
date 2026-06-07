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

@section('styles')
    @include('partials.print-styles')
@endsection

@section('content')
@include('partials.list-export-print', ['exportRoute' => route('materials.export')])
<div class="glass-card printable-area">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 5%">{{ __('dobs.col_id') }}</th>
                    <th style="width: 30%">{{ __('dobs.material_name') }}</th>
                    <th style="width: 45%">{{ __('dobs.description') }}</th>
                    <th style="width: 20%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $material)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('materials.show', $material->id) }}" style="color: whit; font-weight: 600; text-decoration: none;">
                                {{ $material->name }}
                            </a>
                        </td>
                        <td style="color: var(--text-secondary);">{{ Str::limit($material->description, 50) ?: __('dobs.dash') }}</td>
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
                        <td colspan="4" class="empty-state">
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
