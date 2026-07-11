@extends('layouts.app')

@section('title', __('dobs.nav_suppliers'))

@section('header_title', __('dobs.suppliers_title'))
@section('header_subtitle', __('dobs.suppliers_subtitle'))

@section('header_actions')
    @if (auth()->user()?->hasPermission('suppliers', 'create'))
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_supplier') }}
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
                    <th style="width: 25%">{{ __('dobs.col_name') }}</th>
                    <th style="width: 20%">{{ __('dobs.email') }}</th>
                    <th style="width: 15%">{{ __('dobs.phone') }}</th>
                    <th style="width: 20%">{{ __('dobs.address') }}</th>
                    <th style="width: 15%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier->id) }}" style="color: whit; font-weight: 600; text-decoration: none;">
                                {{ $supplier->name }}
                            </a>
                        </td>
                        <td>{{ $supplier->email ?? __('dobs.na') }}</td>
                        <td>{{ $supplier->phone ?? __('dobs.na') }}</td>
                        <td style="color: var(--text-secondary);">{{ $supplier->address ?? __('dobs.na') }}</td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('suppliers.show', $supplier->id),
                                'editRoute' => route('suppliers.edit', $supplier->id),
                                'destroyRoute' => route('suppliers.destroy', $supplier->id),
                                'confirmMessage' => __('dobs.confirm_delete_supplier'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fa-solid fa-truck-field"></i>
                            {{ __('dobs.no_suppliers') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
