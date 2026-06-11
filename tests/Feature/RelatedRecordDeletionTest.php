<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Item;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatedRecordDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_with_items_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::create(['name' => 'Test Category']);
        Item::create([
            'name' => 'Test Item',
            'sku' => 'SKU-001',
            'price' => 10,
            'stock' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category), $this->withDeletePassword());

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error', __('dobs.cannot_delete_has_related'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_without_items_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::create(['name' => 'Empty Category']);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category), $this->withDeletePassword());

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success', __('dobs.flash_category_deleted'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_client_with_operations_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::create(['name' => 'Test Client']);
        Operation::create([
            'operation_number' => 'OFF1',
            'operation_date' => now()->toDateString(),
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('clients.destroy', $client), $this->withDeletePassword());

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('error', __('dobs.cannot_delete_has_related'));
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_operation_status_with_operations_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $status = OperationStatus::create([
            'name' => 'Processing',
            'sort_order' => 1,
            'days' => 3,
            'is_end' => false,
        ]);
        Operation::create([
            'operation_number' => 'OFF2',
            'operation_date' => now()->toDateString(),
            'operation_status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('operation-statuses.destroy', $status), $this->withDeletePassword());

        $response->assertRedirect(route('operation-statuses.index'));
        $response->assertSessionHas('error', __('dobs.cannot_delete_has_related'));
        $this->assertDatabaseHas('operation_statuses', ['id' => $status->id]);
    }
}
