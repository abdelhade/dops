<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientsLazyLoadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_index_paginates_and_returns_ajax_json_payload(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Create 20 clients
        for ($i = 1; $i <= 20; $i++) {
            Client::create([
                'name' => 'Client ' . $i,
                'phone' => '123456789' . $i,
                'email' => 'client' . $i . '@example.com',
            ]);
        }

        // Standard GET request
        $response = $this->actingAs($user)->get(route('clients.index'));
        $response->assertOk();
        // Since we paginate by 15, page 1 should have 15 items
        $this->assertCount(15, $response->viewData('clients'));

        // AJAX GET request for page 2
        $responseAjax = $this->actingAs($user)->get(route('clients.index', ['page' => 2]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $responseAjax->assertOk();
        $responseAjax->assertJsonStructure([
            'html',
            'has_more',
            'next_page_url',
        ]);
        $this->assertFalse($responseAjax->json('has_more'));
    }

    public function test_clients_index_can_be_filtered(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Client::create([
            'name' => 'John Doe',
            'phone' => '111111',
            'email' => 'john@example.com',
            'address' => 'London',
        ]);

        Client::create([
            'name' => 'Jane Smith',
            'phone' => '222222',
            'email' => 'jane@example.com',
            'address' => 'New York',
        ]);

        // Filter by phone
        $response = $this->actingAs($user)->get(route('clients.index', ['phone' => '111111']));
        $response->assertOk();
        $this->assertCount(1, $response->viewData('clients'));
        $this->assertEquals('John Doe', $response->viewData('clients')->first()->name);

        // Filter by email
        $response = $this->actingAs($user)->get(route('clients.index', ['email' => 'jane@example.com']));
        $response->assertOk();
        $this->assertCount(1, $response->viewData('clients'));
        $this->assertEquals('Jane Smith', $response->viewData('clients')->first()->name);

        // Global search
        $response = $this->actingAs($user)->get(route('clients.index', ['search' => 'London']));
        $response->assertOk();
        $this->assertCount(1, $response->viewData('clients'));
        $this->assertEquals('John Doe', $response->viewData('clients')->first()->name);
    }
}
