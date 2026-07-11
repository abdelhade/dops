@extends('layouts.app')

@section('title', __('dobs.nav_stages'))

@section('header_title', __('dobs.stages_title'))
@section('header_subtitle', __('dobs.stages_subtitle'))

@section('header_actions')
    @if (auth()->user()?->hasPermission('stages', 'create'))
        <a href="{{ route('stages.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_stage') }}
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
                    <th style="width: 40%">{{ __('dobs.stage_name') }}</th>
                    <th style="width: 30%">{{ __('dobs.sort_order') }}</th>
                    <th style="width: 25; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stages as $stage)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('stages.show', $stage->id) }}" style="color: whit; font-weight: 600; text-decoration: none;">
                                {{ $stage->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 0.9rem;">
                                {{ $stage->sort_order }}
                            </span>
                        </td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('stages.show', $stage->id),
                                'editRoute' => route('stages.edit', $stage->id),
                                'destroyRoute' => route('stages.destroy', $stage->id),
                                'confirmMessage' => __('dobs.confirm_delete_stage'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fa-solid fa-bars-progress"></i>
                            {{ __('dobs.no_stages') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
