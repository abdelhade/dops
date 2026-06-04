@if ($logs->isNotEmpty())
    <ul class="operation-timeline">
        @foreach ($logs as $log)
            <li class="operation-timeline-item">
                <div class="operation-timeline-marker" aria-hidden="true"></div>
                <div class="operation-timeline-body">
                    <div class="operation-timeline-meta">
                        <time datetime="{{ $log->created_at->toIso8601String() }}">
                            {{ $log->created_at->format('Y-m-d h:i A') }}
                        </time>
                        @if (!empty($showOperation) && $log->operation_id)
                            <a href="{{ route('operations.show', $log->operation_id) }}" class="operation-timeline-operation">
                                <i class="fa-solid fa-file-lines"></i>
                                {{ $log->operation_number ?? $log->operation?->operation_number ?? __('dobs.dash') }}
                            </a>
                        @endif
                        <span class="operation-timeline-user">
                            <i class="fa-solid fa-user"></i>
                            {{ $log->user?->name ?? __('dobs.system_user') }}
                        </span>
                    </div>
                    <div class="operation-timeline-action">{{ $log->actionLabel() }}</div>
                    @if ($log->changeEntries() !== [])
                        <ul class="operation-timeline-changes">
                            @foreach ($log->changeEntries() as $entry)
                                <li class="operation-change-row">
                                    <span class="operation-change-field">{{ $entry['field'] }}</span>
                                    <span class="operation-change-values">
                                        <span class="operation-change-was">
                                            <span class="operation-change-label">{{ __('dobs.log_was') }}</span>
                                            <span class="operation-change-value operation-change-value--from">{{ $entry['from'] }}</span>
                                        </span>
                                        <i class="fa-solid fa-arrow-left-long operation-change-arrow" aria-hidden="true"></i>
                                        <span class="operation-change-now">
                                            <span class="operation-change-label">{{ __('dobs.log_now') }}</span>
                                            <span class="operation-change-value operation-change-value--to">{{ $entry['to'] }}</span>
                                        </span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <ul class="operation-timeline-changes">
                            @foreach ($log->changeLines() as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@else
    <p class="operation-history-empty">{{ $emptyMessage ?? __('dobs.operation_history_empty') }}</p>
@endif
