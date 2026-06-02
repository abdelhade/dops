@extends('layouts.app')

@section('title', __('dobs.nav_activities'))

@section('header_title', __('dobs.activities_title'))
@section('header_subtitle', __('dobs.activities_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('activities.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_activity') }}
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
                    <th style="width: 40%">{{ __('dobs.activity_name') }}</th>
                    <th style="width: 30%">{{ __('dobs.col_description') }}</th>
                    <th style="width: 25; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->id }}</td>
                        <td>
                            <a href="{{ route('activities.show', $activity->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $activity->name }}
                            </a>
                        </td>
                        <td style="color: var(--text-secondary);">{{ $activity->description ?? __('dobs.no_description') }}</td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('activities.show', $activity->id),
                                'editRoute' => route('activities.edit', $activity->id),
                                'destroyRoute' => route('activities.destroy', $activity->id),
                                'confirmMessage' => __('dobs.confirm_delete_activity'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fa-solid fa-list-check"></i>
                            {{ __('dobs.no_activities') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
