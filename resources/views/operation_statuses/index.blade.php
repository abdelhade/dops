@extends('layouts.app')

@section('title', __('dobs.nav_operation_statuses'))

@section('header_title', __('dobs.operation_statuses_title'))
@section('header_subtitle', __('dobs.operation_statuses_subtitle'))

@section('header_actions')
    @if (auth()->user()?->hasPermission('operation-statuses', 'create'))
        <a href="{{ route('operation-statuses.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> {{ __('dobs.new_status') }}
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
                    <th style="width: 25%">{{ __('dobs.status_name') }}</th>
                    <th style="width: 15%">{{ __('dobs.status_color') }}</th>
                    <th style="width: 10%">{{ __('dobs.sort_order') }}</th>
                    <th style="width: 10%">{{ __('dobs.status_days') }}</th>
                    <th style="width: 10%">{{ __('dobs.status_is_end') }}</th>
                    <th style="width: 10%">{{ __('dobs.status_phase_order') }}</th>
                    <th style="width: 15%; text-align: left;">{{ __('dobs.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statuses as $status)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span style="color: {{ $status->color ?? 'inherit' }}; font-weight: 600;">
                                {{ $status->name }}
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $status->color ?? '#6c757d' }}; color: white;">
                                {{ $status->color ?? __('dobs.na') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 0.9rem;">
                                {{ $status->sort_order }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 0.9rem;">
                                {{ $status->days }}
                            </span>
                        </td>
                        <td>
                            @if ($status->is_end)
                                <span class="badge badge-success">{{ __('dobs.yes') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('dobs.no') }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; min-height: 24px;">
                                <input type="number" class="form-control update-phase-order-btn" data-id="{{ $status->id }}" value="{{ $status->phase_order }}" style="width: 70px; text-align: center;">
                            </div>
                        </td>
                        <td>
                            @include('partials.crud-actions', [
                'resource' => 'operation-statuses',
                                'editRoute' => route('operation-statuses.edit', $status->id),
                                'destroyRoute' => route('operation-statuses.destroy', $status->id),
                                'confirmMessage' => __('dobs.confirm_delete_status'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-bars-progress"></i>
                            {{ __('dobs.no_statuses') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = '{{ csrf_token() }}';
        document.querySelectorAll('.update-phase-order-btn').forEach(input => {
            let originalValue = input.value;
            input.addEventListener('change', function () {
                const statusId = this.dataset.id;
                const phaseOrder = this.value;
                
                fetch(`/operation-statuses/${statusId}/update-phase-order`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phase_order: phaseOrder })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        originalValue = phaseOrder;
                    } else {
                        alert('حدث خطأ أثناء التحديث.');
                        this.value = originalValue;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء الاتصال بالخادم.');
                    this.value = originalValue;
                });
            });
        });
    });
</script>
@endsection
