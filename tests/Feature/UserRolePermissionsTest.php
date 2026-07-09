<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_delete(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->assertTrue($admin->canCreateRecords());
        $this->assertTrue($admin->canEditRecords());
        $this->assertTrue($admin->canDeleteRecords());
        $this->assertTrue($admin->canManageUsers());
    }

    public function test_manager_can_create_and_edit_but_not_delete(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $this->assertTrue($manager->canCreateRecords());
        $this->assertTrue($manager->canEditRecords());
        $this->assertFalse($manager->canDeleteRecords());
        $this->assertFalse($manager->canManageUsers());
    }

    public function test_data_entry_can_create_only(): void
    {
        $dataEntry = User::factory()->create(['role' => User::ROLE_DATA_ENTRY]);

        $this->assertTrue($dataEntry->canCreateRecords());
        $this->assertFalse($dataEntry->canEditRecords());
        $this->assertFalse($dataEntry->canDeleteRecords());
        $this->assertFalse($dataEntry->canManageUsers());
    }

    public function test_data_entry_cannot_update_category(): void
    {
        $category = \App\Models\Category::create(['name' => 'Test Category']);
        $dataEntry = User::factory()->create(['role' => User::ROLE_DATA_ENTRY]);

        $response = $this->actingAs($dataEntry)->put(route('categories.update', $category), [
            'name' => 'Updated',
        ]);

        $response->assertForbidden();
    }

    public function test_manager_cannot_delete_category(): void
    {
        $category = \App\Models\Category::create(['name' => 'Test Category']);
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $response = $this->actingAs($manager)->delete(route('categories.destroy', $category));

        $response->assertForbidden();
    }

    public function test_admin_can_assign_permissions_to_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $permission = \Spatie\Permission\Models\Permission::findOrCreate('operations.read');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_DATA_ENTRY,
            'permissions' => ['operations.read'],
        ]);

        $response->assertRedirect(route('users.index'));
        $createdUser = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasDirectPermission('operations.read'));
    }
}
