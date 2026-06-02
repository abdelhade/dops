@extends('layouts.app')

@section('title', __('dobs.nav_paper_sizes'))

@section('header_title', __('dobs.paper_sizes_title'))
@section('header_subtitle', __('dobs.paper_sizes_subtitle'))

@section('header_actions')
<a href="{{ route('paper-sizes.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> {{ __('dobs.new_paper_size') }}
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
                    <th style="width: 25%">{{ __('dobs.width_mm') }}</th>
                    <th style="width: 25%">{{ __('dobs.height_mm') }}</th>
                    <th style="width: 20%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paperSizes as $ps)
                    <tr>
                        <td>{{ $ps->id }}</td>
                        <td>
                            <a href="{{ route('paper-sizes.show', $ps->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                                {{ $ps->name }}
                            </a>
                        </td>
                        <td>{{ $ps->width ? number_format($ps->width, 2) . ' ' . __('dobs.mm_unit') : __('dobs.na') }}</td>
                        <td>{{ $ps->height ? number_format($ps->height, 2) . ' ' . __('dobs.mm_unit') : __('dobs.na') }}</td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('paper-sizes.show', $ps->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.view') }}">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('paper-sizes.edit', $ps->id) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('paper-sizes.destroy', $ps->id) }}" method="POST" onsubmit="return confirm(@json(__('dobs.confirm_delete_paper_size')));" style="display:inline;">
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
