@if (auth()->user()?->canCreateRecords())
<div class="glass-card" style="max-width: {{ $maxWidth ?? '600px' }}; margin: 0 auto 1.5rem;">
    <h3 style="margin: 0 0 0.75rem; font-size: 1.1rem;">
        <i class="fa-solid fa-file-import"></i> {{ __('dobs.import_from_excel') }}
    </h3>
    <p style="color: var(--text-secondary); margin: 0 0 1rem; font-size: 0.9rem;">
        {{ __('dobs.import_from_excel_hint') }}
    </p>
    <form action="{{ $importRoute }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
        @csrf
        <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
            <label for="import_file" class="form-label">{{ __('dobs.import_file') }}</label>
            <input type="file" name="file" id="import_file" class="form-control" accept=".xlsx,.xls,.csv" required>
        </div>
        <a href="{{ $templateRoute }}" class="btn btn-secondary">
            <i class="fa-solid fa-download"></i> {{ __('dobs.download_template') }}
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-upload"></i> {{ __('dobs.import_submit') }}
        </button>
    </form>
</div>
@endif
