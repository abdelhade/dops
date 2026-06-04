<div class="no-print" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
    <button type="button" class="btn btn-secondary" onclick="window.print()">
        <i class="fa-solid fa-print"></i> {{ __('dobs.print') }}
    </button>
    <a href="{{ $exportRoute }}" class="btn btn-secondary">
        <i class="fa-solid fa-file-excel"></i> {{ __('dobs.export_excel') }}
    </a>
</div>
