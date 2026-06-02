@extends('layouts.app')

@section('title', __('dobs.nav_categories'))

@section('header_title', __('dobs.categories_title'))
@section('header_subtitle', __('dobs.categories_subtitle'))

@section('header_actions')
<a href="{{ route('categories.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> {{ __('dobs.new_category') }}
</a>
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
                        <td>{{ $category->id }}</td>
                        <td>
                            <a href="{{ route('categories.show', $category->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $category->name }}
                            </a>
                        </td>
                        <td style="color: var(--text-secondary);">{{ $category->description ?? __('dobs.no_description') }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ __('dobs.items_count', ['count' => $category->items_count]) }}</span>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('categories.show', $category->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.view') }}">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm(@json(__('dobs.confirm_delete_category')));" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
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
