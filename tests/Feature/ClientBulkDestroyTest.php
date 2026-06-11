<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientBulkDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_destroy_deletes_clients_without_related_records(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $clientA = Client::create(['name' => 'Client A']);
        $clientB = Client::create(['name' => 'Client B']);

        $response = $this->actingAs($admin)->postJson(route('clients.bulk-destroy'), $this->withDeletePassword([
            'ids' => [$clientA->id, $clientB->id],
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'deleted' => 2,
            'skipped' => 0,
            'deleted_ids' => [$clientA->id, $clientB->id],
        ]);
        $this->assertDatabaseMissing('clients', ['id' => $clientA->id]);
        $this->assertDatabaseMissing('clients', ['id' => $clientB->id]);
    }

    public function test_bulk_destroy_skips_clients_with_operations(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $deletable = Client::create(['name' => 'Deletable Client']);
        $protected = Client::create(['name' => 'Protected Client']);
        Operation::create([
            'operation_number' => 'OFF-BULK-1',
            'operation_date' => now()->toDateString(),
            'client_id' => $protected->id,
        ]);

        $response = $this->actingAs($admin)->postJson(route('clients.bulk-destroy'), $this->withDeletePassword([
            'ids' => [$deletable->id, $protected->id],
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'deleted' => 1,
            'skipped' => 1,
            'deleted_ids' => [$deletable->id],
        ]);
        $this->assertDatabaseMissing('clients', ['id' => $deletable->id]);
        $this->assertDatabaseHas('clients', ['id' => $protected->id]);
    }

    public function test_bulk_destroy_requires_delete_permission(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $client = Client::create(['name' => 'Client']);

        $response = $this->actingAs($manager)->postJson(route('clients.bulk-destroy'), [
            'ids' => [$client->id],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }
}
