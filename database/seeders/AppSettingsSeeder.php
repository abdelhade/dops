<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (! AppSetting::isDeletePasswordConfigured()) {
            AppSetting::setDeletePassword('00000000');
        }
    }
}
