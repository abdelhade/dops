@extends('layouts.app')

@section('title', __('dobs.nav_activities'))

@section('header_title', __('dobs.activities_title'))
@section('header_subtitle', __('dobs.activities_subtitle'))

@section('content')
<div class="glass-card operation-timeline-card" style="max-width: 960px;">
    @include('partials.operation-log-timeline', [
        'logs' => $logs,
        'showOperation' => true,
        'emptyMessage' => __('dobs.no_activities'),
    ])

    @if ($logs->hasPages())
        <div style="margin-top: 1.5rem;">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
