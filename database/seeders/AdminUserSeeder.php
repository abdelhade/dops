<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@dops.com'],
            [
                'name' => 'مدير النظام',
                'password' => '00000000',
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
