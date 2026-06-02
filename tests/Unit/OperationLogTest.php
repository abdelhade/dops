<?php

namespace Tests\Unit;

use App\Models\OperationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_entries_returns_was_and_now_values(): void
    {
        $log = new OperationLog;
        $log->action = OperationLog::ACTION_STATUS_CHANGED;
        $log->changes = [
            'status' => ['from' => 'Draft', 'to' => 'Processing'],
        ];

        $entries = $log->changeEntries();

        $this->assertCount(1, $entries);
        $this->assertSame(__('dobs.log_field_status'), $entries[0]['field']);
        $this->assertSame(__('dobs.status_draft'), $entries[0]['from']);
        $this->assertSame(__('dobs.status_processing'), $entries[0]['to']);
    }

    public function test_change_line_uses_was_now_wording(): void
    {
        $log = new OperationLog;
        $log->action = OperationLog::ACTION_UPDATED;
        $log->changes = [
            'quantity' => ['from' => 10, 'to' => 20],
        ];

        $line = $log->changeLines()[0];

        $this->assertStringContainsString(__('dobs.log_was'), $line);
        $this->assertStringContainsString(__('dobs.log_now'), $line);
        $this->assertStringContainsString('10', $line);
        $this->assertStringContainsString('20', $line);
    }
}
