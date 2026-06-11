<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OperationKind;
use Illuminate\Database\Seeder;

class OperationKindSeeder extends Seeder
{
    public function run(): void
    {
        $kinds = [
            ['name' => 'سلك سكرين', 'sort_order' => 1],
            ['name' => 'تقطيع', 'sort_order' => 2],
            ['name' => 'تغليف', 'sort_order' => 3],
        ];

        foreach ($kinds as $kind) {
            OperationKind::updateOrCreate(
                ['name' => $kind['name']],
                $kind
            );
        }
    }
}
