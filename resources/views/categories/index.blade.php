@extends('layouts.app')

@section('title', __('dobs.nav_categories'))

@section('header_title', __('dobs.categories_title'))
@section('header_subtitle', __('dobs.categories_subtitle'))

@section('header_actions')
    @if (auth()->user()?->hasPermission('categories', 'create'))
        <a href="{{ route('categories.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_category') }}
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
                    <th style="width: 45%">{{ __('dobs.col_description') }}</th>
                    <th style="width: 10%">{{ __('dobs.col_items_count') }}</th>
                    <th style="width: 15%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('categories.show', $category->id) }}" style="color: whit; font-weight: 600; text-decoration: none;">
                                {{ $category->name }}
                            </a>
                        </td>
                        <td style="color: var(--text-secondary);">{{ $category->description ?? __('dobs.no_description') }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ __('dobs.items_count', ['count' => $category->items_count]) }}</span>
                        </td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('categories.show', $category->id),
                                'editRoute' => route('categories.edit', $category->id),
                                'destroyRoute' => route('categories.destroy', $category->id),
                                'confirmMessage' => __('dobs.confirm_delete_category'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fa-solid fa-tags"></i>
                            {{ __('dobs.no_categories') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
