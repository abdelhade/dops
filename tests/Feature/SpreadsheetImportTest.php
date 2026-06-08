<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SpreadsheetImportTest extends TestCase
{
    use RefreshDatabase;

    private function makeXlsx(array $headers, array $dataRows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$headers], null, 'A1');

        if ($dataRows !== []) {
            $sheet->fromArray($dataRows, null, 'A2');
        }

        $path = tempnam(sys_get_temp_dir(), 'import_test_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    public function test_clients_import_creates_records(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $file = $this->makeXlsx(
            ['Name', 'Phone', 'Email', 'Address', 'Notes'],
            [['Imported Client', '0500000001', 'imported@example.com', 'Address', 'Note']]
        );

        $response = $this->actingAs($user)->post(route('clients.import'), ['file' => $file]);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', ['name' => 'Imported Client', 'email' => 'imported@example.com']);
    }

    public function test_items_import_creates_records(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $file = $this->makeXlsx(
            ['Name', 'SKU', 'Description', 'Category', 'Supplier', 'Paper Size', 'Price', 'Stock'],
            [['Imported Item', '', '', '', '', '', '10', '5']]
        );

        $response = $this->actingAs($user)->post(route('items.import'), ['file' => $file]);

        $response->assertRedirect(route('items.index'));
        $this->assertDatabaseHas('items', ['name' => 'Imported Item', 'price' => 10, 'stock' => 5]);
    }

    public function test_clients_create_page_shows_import_form(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->get(route('clients.create'));

        $response->assertOk();
        $response->assertSee(route('clients.import'), false);
        $response->assertSee('import_file_clients', false);
    }

    public function test_items_create_page_shows_import_form(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->get(route('items.create'));

        $response->assertOk();
        $response->assertSee(route('items.import'), false);
        $response->assertSee('import_file_items', false);
    }
}
