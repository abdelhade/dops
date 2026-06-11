<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_settings_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertSee(__('dobs.settings_title'), false);
    }

    public function test_non_admin_cannot_view_settings_page(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $response = $this->actingAs($manager)->get(route('settings.edit'));

        $response->assertForbidden();
    }

    public function test_admin_can_update_delete_password(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'delete_password' => 'new-secret',
            'delete_password_confirmation' => 'new-secret',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHas('success', __('dobs.flash_settings_updated'));
        $this->assertTrue(AppSetting::verifyDeletePassword('new-secret'));
    }

    public function test_delete_requires_valid_password(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = \App\Models\Category::create(['name' => 'To Delete']);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category), [
            'delete_password' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('dobs.delete_password_invalid'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
