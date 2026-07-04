@extends('layouts.app')

@section('title', __('dobs.daftara_sync'))

@section('header_title', __('dobs.daftara_sync'))
@section('header_subtitle', __('dobs.daftara_sync_subtitle'))

@section('header_actions')
<a href="{{ route('clients.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('styles')
<style>
    .sync-table-container {
        margin-top: 1rem;
    }
    .client-checkbox {
        width: 1.15rem;
        height: 1.15rem;
        cursor: pointer;
        accent-color: var(--color-primary);
    }
</style>
@endsection

@section('content')
<div class="glass-card">
    @if(empty($missingClients))
        <div class="empty-state" style="padding: 3rem 1rem;">
            <i class="fa-solid fa-circle-check" style="color: var(--color-success); font-size: 3rem; margin-bottom: 1rem;"></i>
            <h3>{{ __('dobs.daftara_all_synced') }}</h3>
            <p style="color: var(--text-secondary); margin-top: 0.5rem;">{{ __('dobs.daftara_all_synced_hint') }}</p>
            <a href="{{ route('clients.index') }}" class="btn btn-primary" style="margin-top: 1.5rem;">
                {{ __('dobs.back_to_list') }}
            </a>
        </div>
    @else
        <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <p style="color: var(--text-secondary); margin: 0;">
                {{ __('dobs.daftara_found_count', ['count' => count($missingClients)]) }}
            </p>
        </div>

        <form action="{{ route('clients.daftara.sync') }}" method="POST" id="syncForm">
            @csrf

            <div class="table-container sync-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">
                                <input type="checkbox" id="selectAllSync" class="client-checkbox" checked>
                            </th>
                            <th style="width: 25%">{{ __('dobs.client_name') }}</th>
                            <th style="width: 25%">{{ __('dobs.email') }}</th>
                            <th style="width: 20%">{{ __('dobs.phone') }}</th>
                            <th style="width: 25%">{{ __('dobs.address') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missingClients as $index => $client)
                            <tr class="client-row">
                                <td style="text-align: center;">
                                    <input type="checkbox" class="client-checkbox row-checkbox" checked>
                                    
                                    {{-- Hidden inputs containing client data --}}
                                    <input type="hidden" name="clients[{{ $index }}][name]" value="{{ $client['name'] }}">
                                    <input type="hidden" name="clients[{{ $index }}][phone]" value="{{ $client['phone'] }}">
                                    <input type="hidden" name="clients[{{ $index }}][email]" value="{{ $client['email'] }}">
                                    <input type="hidden" name="clients[{{ $index }}][address]" value="{{ $client['address'] }}">
                                    <input type="hidden" name="clients[{{ $index }}][notes]" value="{{ $client['notes'] }}">
                                </td>
                                <td>
                                    <strong>{{ $client['name'] }}</strong>
                                </td>
                                <td>{{ $client['email'] ?? __('dobs.na') }}</td>
                                <td>{{ $client['phone'] ?? __('dobs.na') }}</td>
                                <td style="color: var(--text-secondary);">{{ $client['address'] ?? __('dobs.na') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fa-solid fa-cloud-arrow-down"></i> {{ __('dobs.sync_now') }}
                </button>
            </div>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllSync = document.getElementById('selectAllSync');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const syncForm = document.getElementById('syncForm');
        const submitBtn = document.getElementById('submitBtn');

        if (selectAllSync) {
            selectAllSync.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAllSync.checked;
                });
                updateSubmitButtonState();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                if (!this.checked && selectAllSync) {
                    selectAllSync.checked = false;
                } else if (Array.from(rowCheckboxes).every(c => c.checked) && selectAllSync) {
                    selectAllSync.checked = true;
                }
                updateSubmitButtonState();
            });
        });

        function updateSubmitButtonState() {
            const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            if (submitBtn) {
                submitBtn.disabled = !anyChecked;
            }
        }

        if (syncForm) {
            syncForm.addEventListener('submit', function () {
                const rows = document.querySelectorAll('.client-row');
                rows.forEach(row => {
                    const checkbox = row.querySelector('.row-checkbox');
                    if (!checkbox.checked) {
                        row.querySelectorAll('input[type="hidden"]').forEach(input => {
                            input.disabled = true;
                        });
                    }
                });
            });
        }
    });
</script>
@endsection
