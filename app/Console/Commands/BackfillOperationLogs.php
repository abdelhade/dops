<?php

namespace App\Console\Commands;

use App\Models\Operation;
use App\Models\OperationLog;
use Illuminate\Console\Command;

class BackfillOperationLogs extends Command
{
    protected $signature = 'operations:backfill-logs';

    protected $description = 'Create initial activity log entries for operations that have no history';

    public function handle(): int
    {
        $created = 0;

        Operation::query()
            ->whereDoesntHave('logs')
            ->orderBy('id')
            ->each(function (Operation $operation) use (&$created) {
                OperationLog::create([
                    'operation_id' => $operation->id,
                    'operation_number' => $operation->operation_number,
                    'user_id' => null,
                    'action' => OperationLog::ACTION_CREATED,
                    'changes' => null,
                    'created_at' => $operation->created_at,
                    'updated_at' => $operation->created_at,
                ]);

                $created++;
            });

        $this->info("Created {$created} activity log entr".($created === 1 ? 'y' : 'ies').'.');

        return self::SUCCESS;
    }
}
