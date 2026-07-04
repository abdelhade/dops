@forelse($clients as $client)
    <tr>
        @if ($canBulkDelete)
            <td style="text-align: center;">
                <input
                    type="checkbox"
                    class="clients-bulk-checkbox clients-bulk-row-cb"
                    value="{{ $client->id }}"
                    aria-label="{{ __('dobs.bulk_select_item', ['name' => $client->name]) }}"
                >
            </td>
        @endif
        <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
        <td>
            <a href="{{ route('clients.show', $client->id) }}" style="color: white; font-weight: 600; text-decoration: none;">
                {{ $client->name }}
            </a>
        </td>
        <td>{{ $client->email ?? __('dobs.na') }}</td>
        <td>{{ $client->phone ?? __('dobs.na') }}</td>
        <td style="color: var(--text-secondary);">{{ $client->address ?? __('dobs.na') }}</td>
        <td>
            @include('partials.crud-actions', [
                'showRoute' => route('clients.show', $client->id),
                'editRoute' => route('clients.edit', $client->id),
                'destroyRoute' => route('clients.destroy', $client->id),
                'confirmMessage' => __('dobs.confirm_delete_client'),
            ])
        </td>
    </tr>
@empty
    @if ($clients->currentPage() === 1)
        <tr>
            <td colspan="{{ $canBulkDelete ? 7 : 6 }}" class="empty-state">
                <i class="fa-solid fa-user-tie"></i>
                {{ __('dobs.no_clients') }}
            </td>
        </tr>
    @endif
@endforelse
