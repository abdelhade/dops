<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Item;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\OperationType;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationServiceAndDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_operation_with_service_4_and_dates(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::create(['name' => 'Test Client']);
        $item = Item::create(['name' => 'Test Item', 'price' => 10, 'stock' => 100]);
        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $status = OperationStatus::create(['name' => 'Draft', 'sort_order' => 1]);
        $service = Service::create(['name' => 'Test Service']);

        // Fetch/create operation types
        $offsetType = OperationType::where('slug', 'offset')->first() ?? OperationType::create([
            'name' => 'Offset',
            'slug' => 'offset',
            'form_mode' => \App\Enums\OperationTypeMode::Offset,
            'serial_prefix' => 'OF',
        ]);

        $postData = [
            'operation_type_id' => $offsetType->id,
            'operation_number' => 'OF100',
            'operation_date' => '2026-07-08',
            'operation_time' => '12:00',
            'client_id' => $client->id,
            'item_id' => $item->id,
            'quantity' => 10,
            'printing_supplier_id' => $supplier->id,
            'printing_in_date' => '2026-07-01',
            'printing_out_date' => '2026-07-05',
            'color_count' => 4,
            'service_1_id' => $service->id,
            'service_1_in_date' => '2026-07-02',
            'service_1_out_date' => '2026-07-03',
            'service_4_id' => $service->id,
            'service_4_in_date' => '2026-07-04',
            'service_4_out_date' => '2026-07-05',
            'operation_status_id' => $status->id,
        ];

        $response = $this->actingAs($user)->post(route('operations.store'), $postData);

        $response->assertRedirect();
        
        $operation = Operation::where('operation_number', 'OF100')->first();
        $this->assertNotNull($operation);
        $this->assertEquals('2026-07-01', $operation->printing_in_date->format('Y-m-d'));
        $this->assertEquals('2026-07-05', $operation->printing_out_date->format('Y-m-d'));
        $this->assertEquals($service->id, $operation->service_4_id);
        $this->assertEquals('2026-07-04', $operation->service_4_in_date->format('Y-m-d'));
        $this->assertEquals('2026-07-05', $operation->service_4_out_date->format('Y-m-d'));
    }

    public function test_can_save_general_operation_with_entry_and_exit_dates(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::create(['name' => 'Test Client']);
        $item = Item::create(['name' => 'Test Item', 'price' => 10, 'stock' => 100]);
        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $status = OperationStatus::create(['name' => 'Draft', 'sort_order' => 1]);

        $generalType = OperationType::where('slug', 'general')->first() ?? OperationType::create([
            'name' => 'General',
            'slug' => 'general',
            'form_mode' => \App\Enums\OperationTypeMode::General,
            'serial_prefix' => 'GN',
        ]);

        $kind = \App\Models\OperationKind::create(['name' => 'General Kind']);

        $postData = [
            'operation_type_id' => $generalType->id,
            'operation_number' => 'GN100',
            'operation_date' => '2026-07-08',
            'operation_time' => '12:00',
            'client_id' => $client->id,
            'item_id' => $item->id,
            'quantity' => 10,
            'printing_supplier_id' => $supplier->id,
            'color_count' => 4,
            'operation_kind_id' => $kind->id,
            'stencil' => \App\Enums\OperationStencil::New->value,
            'silk_unit' => \App\Enums\OperationSilkUnit::Kilo->value,
            'entry_date' => '2026-07-01',
            'exit_date' => '2026-07-05',
            'operation_status_id' => $status->id,
        ];

        $response = $this->actingAs($user)->post(route('operations.store'), $postData);

        $response->assertRedirect();

        $operation = Operation::where('operation_number', 'GN100')->first();
        $this->assertNotNull($operation);
        $this->assertEquals('2026-07-01', $operation->entry_date->format('Y-m-d'));
        $this->assertEquals('2026-07-05', $operation->exit_date->format('Y-m-d'));
    }
}
