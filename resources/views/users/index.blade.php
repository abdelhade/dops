@extends('layouts.app')

@section('title', __('dobs.nav_users'))

@section('header_title', __('dobs.users_title'))
@section('header_subtitle', __('dobs.users_subtitle'))

@section('header_actions')
<a href="{{ route('users.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-user-plus"></i> {{ __('dobs.new_user') }}
</a>
@endsection

@section('content')
<div class="glass-card">
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>{{ __('dobs.col_name') }}</th>
                    <th>{{ __('dobs.email') }}</th>
                    <th>{{ __('dobs.col_role') }}</th>
                    <th>{{ __('dobs.col_created_at') }}</th>
                    <th style="text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td style="font-weight: 600; color: white;">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-role-{{ $user->role }}">{{ $user->roleLabel() }}</span>
                        </td>
                        <td style="color: var(--text-secondary);">{{ $user->created_at?->format('Y-m-d') }}</td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm(@json(__('dobs.confirm_delete_user')));" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fa-solid fa-users"></i>
                            {{ __('dobs.no_users') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
