<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OperationTypeMode;
use App\Models\OperationType;
use Illuminate\Database\Seeder;

class OperationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'OFFSET',
                'slug' => 'offset',
                'form_mode' => OperationTypeMode::Offset->value,
                'serial_prefix' => 'OFF',
                'sort_order' => 1,
                'is_system' => true,
            ],
            [
                'name' => 'عام',
                'slug' => 'general',
                'form_mode' => OperationTypeMode::General->value,
                'serial_prefix' => 'SS',
                'sort_order' => 2,
                'is_system' => true,
            ],
        ];

        foreach ($types as $type) {
            OperationType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }

        OperationType::query()->where('slug', 'silk_screen')->delete();
        OperationType::query()->where('form_mode', 'silk_screen')->update(['form_mode' => OperationTypeMode::General->value]);
    }
}
