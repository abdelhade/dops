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
            ['email' => 'admin@dobs.local'],
            [
                'name' => 'مدير النظام',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
