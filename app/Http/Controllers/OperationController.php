<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Material;
use App\Models\Operation;
use App\Models\Service;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OperationController extends Controller
{
    public function index()
    {
        $operations = Operation::with('item')
            ->latest()
            ->get();

        return view('operations.index', compact('operations'));
    }

    public function create()
    {
        $this->authorizeCreate();

        $opNumber = 'off-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        return view('operations.create', array_merge(
            $this->formOptions(),
            ['opNumber' => $opNumber]
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate($this->validationRules());

        try {
            DB::beginTransaction();

            $operation = Operation::create($this->mapValidatedToAttributes($validated));

            if ($validated['status'] === 'Completed') {
                $this->deductItemStock($validated['item_id'], (int) $validated['quantity']);
            }

            DB::commit();

            return redirect()->route('operations.index')->with('success', __('dobs.flash_operation_created'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', __('dobs.flash_operation_create_error', ['message' => $e->getMessage()]));
        }
    }

    public function show(Operation $operation)
    {
        $operation->load([
            'item.category',
            'item.paperSize',
            'printingSupplier',
            'ctpSupplier',
            'material',
            'service1',
            'service2',
            'service3',
            'items.category',
            'items.paperSize',
        ]);

        return view('operations.show', compact('operation'));
    }

    public function edit(Operation $operation)
    {
        $this->authorizeEdit();

        if ($operation->status === 'Completed') {
            return redirect()->route('operations.index')
                ->with('error', __('dobs.flash_operation_completed_locked'));
        }

        return view('operations.edit', array_merge(
            $this->formOptions(),
            ['operation' => $operation]
        ));
    }

    public function update(Request $request, Operation $operation)
    {
        $this->authorizeEdit();

        if ($operation->status === 'Completed') {
            return redirect()->route('operations.index')
                ->with('error', __('dobs.flash_operation_completed_locked'));
        }

        $validated = $request->validate($this->validationRules($operation));

        try {
            DB::beginTransaction();

            $wasCompleted = $operation->status === 'Completed';

            $operation->update($this->mapValidatedToAttributes($validated));

            if (! $wasCompleted && $validated['status'] === 'Completed') {
                $this->deductItemStock($validated['item_id'], (int) $validated['quantity']);
            }

            DB::commit();

            return redirect()->route('operations.index')->with('success', __('dobs.flash_operation_updated'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', __('dobs.flash_operation_update_error', ['message' => $e->getMessage()]));
        }
    }

    public function destroy(Operation $operation)
    {
        $this->authorizeDelete();

        try {
            DB::beginTransaction();

            $operation->items()->detach();
            $operation->delete();

            DB::commit();

            return redirect()->route('operations.index')->with('success', __('dobs.flash_operation_deleted'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('operations.index')->with('error', __('dobs.flash_operation_delete_error', ['message' => $e->getMessage()]));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'items' => Item::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'materials' => Material::orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(?Operation $operation = null): array
    {
        $operationId = $operation?->id;

        return [
            'operation_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('operations', 'operation_number')->ignore($operationId),
            ],
            'operation_date' => 'required|date',
            'operation_time' => 'required|date_format:H:i',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'statement' => 'nullable|string',
            'printing_supplier_id' => 'nullable|exists:suppliers,id',
            'ctp_supplier_id' => 'nullable|exists:suppliers,id',
            'color_count' => 'required|integer|min:1|max:10',
            'material_id' => 'nullable|exists:materials,id',
            'job_size' => 'nullable|numeric|min:0',
            'pull_count' => 'nullable|integer|min:0',
            'quantity_per_sheet' => 'nullable|integer|min:0',
            'service_1_id' => 'nullable|exists:services,id',
            'service_2_id' => 'nullable|exists:services,id',
            'service_3_id' => 'nullable|exists:services,id',
            'status' => 'required|in:Draft,Processing,Completed',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapValidatedToAttributes(array $validated): array
    {
        return [
            'operation_number' => $validated['operation_number'],
            'operation_date' => $validated['operation_date'],
            'operation_time' => $validated['operation_time'] . ':00',
            'item_id' => $validated['item_id'],
            'quantity' => $validated['quantity'],
            'statement' => $validated['statement'] ?? null,
            'printing_supplier_id' => $validated['printing_supplier_id'] ?? null,
            'ctp_supplier_id' => $validated['ctp_supplier_id'] ?? null,
            'color_count' => $validated['color_count'],
            'material_id' => $validated['material_id'] ?? null,
            'job_size' => $validated['job_size'] ?? null,
            'pull_count' => $validated['pull_count'] ?? null,
            'quantity_per_sheet' => $this->calcQuantityPerSheet($validated),
            'service_1_id' => $validated['service_1_id'] ?? null,
            'service_2_id' => $validated['service_2_id'] ?? null,
            'service_3_id' => $validated['service_3_id'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['statement'] ?? null,
            'total_amount' => 0,
        ];
    }

    private function deductItemStock(int $itemId, int $quantity): void
    {
        $item = Item::find($itemId);

        if ($item) {
            $item->decrement('stock', $quantity);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function calcQuantityPerSheet(array $validated): ?int
    {
        $jobSize = $validated['job_size'] ?? null;
        $pullCount = $validated['pull_count'] ?? null;

        if ($jobSize === null || $pullCount === null || (float) $jobSize <= 0) {
            return null;
        }

        return (int) ceil((int) $pullCount / (float) $jobSize);
    }
}
