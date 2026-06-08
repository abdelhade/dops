<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Item;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationFormOptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_quick_create_client(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->postJson(route('operations.form-options.store'), [
            'type' => 'client',
            'name' => 'عميل جديد',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'name']);

        $this->assertDatabaseHas('clients', ['name' => 'عميل جديد']);
    }

    public function test_quick_create_returns_existing_client_instead_of_duplicate(): void
    {
        $client = Client::create(['name' => 'Existing Client']);
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->postJson(route('operations.form-options.store'), [
            'type' => 'client',
            'name' => 'existing client',
        ]);

        $response->assertOk()
            ->assertJson([
                'id' => $client->id,
                'name' => 'Existing Client',
            ]);

        $this->assertSame(1, Client::count());
    }

    public function test_quick_create_item_uses_defaults(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->postJson(route('operations.form-options.store'), [
            'type' => 'item',
            'name' => 'صنف سريع',
        ]);

        $response->assertOk();

        $item = Item::query()->where('name', 'صنف سريع')->first();

        $this->assertNotNull($item);
        $this->assertSame(0.0, (float) $item->price);
        $this->assertSame(0, $item->stock);
        $this->assertNotEmpty($item->sku);
    }

    public function test_quick_create_supplier_and_service(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($user)->postJson(route('operations.form-options.store'), [
            'type' => 'supplier',
            'name' => 'مطبعة جديدة',
        ])->assertOk();

        $this->actingAs($user)->postJson(route('operations.form-options.store'), [
            'type' => 'service',
            'name' => 'تجليد سريع',
        ])->assertOk();

        $this->assertDatabaseHas('suppliers', ['name' => 'مطبعة جديدة']);
        $this->assertDatabaseHas('services', ['name' => 'تجليد سريع']);
    }

    public function test_quick_create_operation_status_sets_sort_order(): void
    {
        \App\Models\OperationStatus::create(['name' => 'Draft', 'sort_order' => 3]);
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->postJson(route('operations.form-options.store'), [
            'type' => 'operation_status',
            'name' => 'حالة جديدة',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'name']);

        $this->assertDatabaseHas('operation_statuses', [
            'name' => 'حالة جديدة',
            'sort_order' => 4,
        ]);
    }

    public function test_guest_cannot_quick_create_option(): void
    {
        $response = $this->postJson(route('operations.form-options.store'), [
            'type' => 'client',
            'name' => 'Test',
        ]);

        $response->assertUnauthorized();
    }
}
