<?php

namespace Tests\Feature;

use App\Models\Operation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaperMaterialsSummaryExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_export_paper_materials_summary(): void
    {
        $user = User::factory()->create();

        // Create an operation
        $operation = Operation::create([
            'operation_number' => 'OFF1',
            'operation_date' => now()->toDateString(),
        ]);

        // Request export
        $response = $this->actingAs($user)->get(route('reports.paper-materials-summary.export', [
            'applied' => 1,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=paper_materials_summary.xlsx');
    }
}
