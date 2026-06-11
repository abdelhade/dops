@props([
    'showRoute' => null,
    'editRoute' => null,
    'destroyRoute' => null,
    'confirmMessage' => null,
])

<div class="actions-cell">
    @if ($showRoute)
        <a href="{{ $showRoute }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.view') }}">
            <i class="fa-solid fa-eye"></i>
        </a>
    @endif

    @if ($editRoute && auth()->user()?->canEditRecords())
        <a href="{{ $editRoute }}" class="btn btn-secondary btn-sm" title="{{ __('dobs.edit') }}">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
    @endif

    @if ($destroyRoute && auth()->user()?->canDeleteRecords())
        <form
            action="{{ $destroyRoute }}"
            method="POST"
            class="dobs-delete-form"
            data-dobs-delete
            data-dobs-confirm="{{ $confirmMessage }}"
            style="display:inline;"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" title="{{ __('dobs.delete') }}">
                <i class="fa-solid fa-trash"></i>
            </button>
        </form>
    @endif
</div>
