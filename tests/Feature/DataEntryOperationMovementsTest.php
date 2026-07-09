<?php

namespace Tests\Feature;

use App\Models\Operation;
use App\Models\OperationMovement;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataEntryOperationMovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_entry_user_is_redirected_to_operation_movements_after_login(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_DATA_ENTRY,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('operation-movements.index'));
    }

    public function test_data_entry_user_is_redirected_from_dashboard_to_operation_movements(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_DATA_ENTRY]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('operation-movements.index'));
    }

    public function test_data_entry_user_only_sees_movements_for_assigned_services(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_DATA_ENTRY]);
        
        $serviceA = Service::create(['name' => 'Service A']);
        $serviceB = Service::create(['name' => 'Service B']);
        
        $user->services()->attach($serviceA->id);

        $operation = Operation::create([
            'operation_number' => 'OP-001',
            'operation_date' => now()->toDateString(),
            'service_1_id' => $serviceA->id,
            'service_2_id' => $serviceB->id,
        ]);

        $movementA = OperationMovement::create([
            'operation_id' => $operation->id,
            'service_id' => $serviceA->id,
            'type' => 'entry',
            'datetime' => now(),
        ]);

        $movementB = OperationMovement::create([
            'operation_id' => $operation->id,
            'service_id' => $serviceB->id,
            'type' => 'entry',
            'datetime' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('operation-movements.index'));

        $response->assertStatus(200);
        $response->assertSee($serviceA->name);
        $response->assertDontSee($serviceB->name);
    }

    public function test_data_entry_user_only_sees_operations_containing_assigned_services_in_create_form(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_DATA_ENTRY]);
        
        $serviceA = Service::create(['name' => 'Service A']);
        $serviceB = Service::create(['name' => 'Service B']);
        
        $user->services()->attach($serviceA->id);

        $opA = Operation::create([
            'operation_number' => 'OP-AAA',
            'operation_date' => now()->toDateString(),
            'service_1_id' => $serviceA->id,
        ]);

        $opB = Operation::create([
            'operation_number' => 'OP-BBB',
            'operation_date' => now()->toDateString(),
            'service_1_id' => $serviceB->id,
        ]);

        $response = $this->actingAs($user)->get(route('operation-movements.create'));

        $response->assertStatus(200);
        $response->assertSee('OP-AAA');
        $response->assertDontSee('OP-BBB');
    }

    public function test_data_entry_user_only_sees_operations_containing_assigned_services_in_operations_index(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_DATA_ENTRY]);
        $serviceA = Service::create(['name' => 'Service A']);
        $serviceB = Service::create(['name' => 'Service B']);
        $user->services()->attach($serviceA->id);

        $type = \App\Models\OperationType::where('slug', 'offset')->first() ?? \App\Models\OperationType::create([
            'name' => 'Offset',
            'slug' => 'offset',
            'form_mode' => \App\Enums\OperationTypeMode::Offset,
            'serial_prefix' => 'OFF',
        ]);

        $opA = Operation::create([
            'operation_number' => 'OP-AAA',
            'operation_date' => now()->toDateString(),
            'operation_type_id' => $type->id,
            'service_1_id' => $serviceA->id,
        ]);

        $opB = Operation::create([
            'operation_number' => 'OP-BBB',
            'operation_date' => now()->toDateString(),
            'operation_type_id' => $type->id,
            'service_1_id' => $serviceB->id,
        ]);

        $response = $this->actingAs($user)->get(route('operations.index', ['type' => 'offset']));

        $response->assertStatus(200);
        $response->assertSee('OP-AAA');
        $response->assertDontSee('OP-BBB');
    }

    public function test_data_entry_user_cannot_view_unassigned_operation_details(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_DATA_ENTRY]);
        $serviceA = Service::create(['name' => 'Service A']);
        $serviceB = Service::create(['name' => 'Service B']);
        $user->services()->attach($serviceA->id);

        $opB = Operation::create([
            'operation_number' => 'OP-BBB',
            'operation_date' => now()->toDateString(),
            'service_1_id' => $serviceB->id,
        ]);

        $response = $this->actingAs($user)->get(route('operations.show', $opB));

        $response->assertForbidden();
    }
}
