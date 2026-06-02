@extends('layouts.app')

@section('title', __('dobs.nav_paper_types'))

@section('header_title', __('dobs.paper_types_title'))
@section('header_subtitle', __('dobs.paper_types_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('paper-types.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_paper_type') }}
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
                    <th style="width: 30%">{{ __('dobs.paper_type_name') }}</th>
                    <th style="width: 20%">{{ __('dobs.weight_gsm') }}</th>
                    <th style="width: 20%">{{ __('dobs.finish') }}</th>
                    <th style="width: 25; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paperTypes as $paperType)
                    <tr>
                        <td>{{ $paperType->id }}</td>
                        <td>
                            <a href="{{ route('paper-types.show', $paperType->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $paperType->name }}
                            </a>
                        </td>
                        <td>{{ $paperType->weight_gsm ? $paperType->weight_gsm . ' gsm' : __('dobs.na') }}</td>
                        <td>{{ $paperType->finish ?? __('dobs.na') }}</td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('paper-types.show', $paperType->id),
                                'editRoute' => route('paper-types.edit', $paperType->id),
                                'destroyRoute' => route('paper-types.destroy', $paperType->id),
                                'confirmMessage' => __('dobs.confirm_delete_paper_type'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fa-solid fa-scroll"></i>
                            {{ __('dobs.no_paper_types') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
