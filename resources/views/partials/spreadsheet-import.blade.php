@if (auth()->user()?->canCreateRecords())
    @php($importInputId = 'import_file_' . ($importId ?? 'default'))
    @if (!empty($compact))
        <div class="spreadsheet-import-compact" style="max-width: {{ $maxWidth ?? '850px' }};">
            <a href="{{ $templateRoute }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-download"></i> {{ __('dobs.download_template') }}
            </a>
            <form action="{{ $importRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" id="{{ $importInputId }}" class="spreadsheet-import-file" accept=".xlsx,.xls,.csv" onchange="if (this.files.length) this.form.submit()">
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('{{ $importInputId }}').click()">
                    <i class="fa-solid fa-upload"></i> {{ __('dobs.import_submit') }}
                </button>
            </form>
        </div>
    @else
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
                    <label for="{{ $importInputId }}" class="form-label">{{ __('dobs.import_file') }}</label>
                    <input type="file" name="file" id="{{ $importInputId }}" class="form-control" accept=".xlsx,.xls,.csv" required>
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
@endif
