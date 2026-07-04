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
}
