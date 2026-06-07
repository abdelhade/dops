@extends('layouts.app')

@section('title', __('dobs.nav_paper_sizes'))

@section('header_title', __('dobs.paper_sizes_title'))
@section('header_subtitle', __('dobs.paper_sizes_subtitle'))

@section('header_actions')
    @if (auth()->user()?->canCreateRecords())
        <a href="{{ route('paper-sizes.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_paper_size') }}
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
                    <th style="width: 25%">{{ __('dobs.width_mm') }}</th>
                    <th style="width: 25%">{{ __('dobs.height_mm') }}</th>
                    <th style="width: 20%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paperSizes as $ps)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('paper-sizes.show', $ps->id) }}" style="color: whit; font-weight: 600; text-decoration: none;">
                                {{ $ps->name }}
                            </a>
                        </td>
                        <td>{{ $ps->width ? number_format($ps->width, 2) . ' ' . __('dobs.mm_unit') : __('dobs.na') }}</td>
                        <td>{{ $ps->height ? number_format($ps->height, 2) . ' ' . __('dobs.mm_unit') : __('dobs.na') }}</td>
                        <td>
                            @include('partials.crud-actions', [
                                'showRoute' => route('paper-sizes.show', $ps->id),
                                'editRoute' => route('paper-sizes.edit', $ps->id),
                                'destroyRoute' => route('paper-sizes.destroy', $ps->id),
                                'confirmMessage' => __('dobs.confirm_delete_paper_size'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fa-solid fa-maximize"></i>
                            {{ __('dobs.no_paper_sizes') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
