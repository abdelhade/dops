<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\OperationStatus;
use App\Models\PaperType;
use App\Models\Service;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperationFormOptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['client', 'item', 'supplier', 'paper_type', 'service', 'operation_status'])],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);

        $record = match ($validated['type']) {
            'client' => $this->firstOrCreateByName(Client::class, $name),
            'item' => $this->firstOrCreateItem($name),
            'supplier' => $this->firstOrCreateByName(Supplier::class, $name),
            'paper_type' => $this->firstOrCreateByName(PaperType::class, $name),
            'service' => $this->firstOrCreateService($name),
            'operation_status' => $this->firstOrCreateStatus($name),
        };

        return response()->json([
            'id' => $record->id,
            'name' => $record->name,
        ]);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function firstOrCreateByName(string $modelClass, string $name): Model
    {
        $existing = $modelClass::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return $modelClass::query()->create(['name' => $name]);
    }

    private function firstOrCreateItem(string $name): Item
    {
        $existing = Item::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return Item::query()->create([
            'name' => $name,
            'sku' => 'SKU-' . strtoupper(uniqid()),
            'price' => 0,
            'stock' => 0,
        ]);
    }

    private function firstOrCreateService(string $name): Service
    {
        $existing = Service::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return Service::query()->create([
            'name' => $name,
            'price' => 0,
        ]);
    }

    private function firstOrCreateStatus(string $name): OperationStatus
    {
        $existing = OperationStatus::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return OperationStatus::query()->create([
            'name' => $name,
            'sort_order' => (int) OperationStatus::query()->max('sort_order') + 1,
        ]);
    }
}
