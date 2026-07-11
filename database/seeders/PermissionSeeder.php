<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = [
            'operations',
            'operation-movements',
            'clients',
            'items',
            'materials',
            'suppliers',
            'operation-statuses',
            'users',
            'categories',
        ];

        $actions = ['read', 'create', 'update', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$resource}.{$action}");
            }
        }
    }
}
