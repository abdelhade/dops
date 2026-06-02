<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Material;
use App\Models\Operation;
use App\Models\OperationLog;
use App\Models\Service;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationController extends Controller
{
    /** @var list<string> */
    private const TRACKABLE_FIELDS = [
        'status',
        'operation_number',
        'operation_date',
        'operation_time',
        'item_id',
        'quantity',
        'statement',
        'printing_supplier_id',
        'ctp_supplier_id',
        'color_count',
        'material_id',
        'job_size',
        'pull_count',
        'quantity_per_sheet',
        'service_1_id',
        'service_2_id',
        'service_3_id',
    ];

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

            $this->logOperation($operation, OperationLog::ACTION_CREATED);

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
            'logs.user',
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

            $before = $operation->only(self::TRACKABLE_FIELDS);
            $oldStatus = $operation->status;

            $operation->update($this->mapValidatedToAttributes($validated));

            $this->applyStatusTransition($operation, $validated['status'], $oldStatus);

            $changes = $this->diffChanges($before, $operation->only(self::TRACKABLE_FIELDS));
            if ($changes !== []) {
                $this->logOperation($operation, OperationLog::ACTION_UPDATED, $changes);
            }

            DB::commit();

            return redirect()->route('operations.index')->with('success', __('dobs.flash_operation_updated'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', __('dobs.flash_operation_update_error', ['message' => $e->getMessage()]));
        }
    }

    public function updateStatus(Request $request, Operation $operation)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'status' => 'required|in:Draft,Processing,Completed',
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $operation->status;

        if ($oldStatus === $newStatus) {
            return redirect()->route('operations.index');
        }

        try {
            DB::beginTransaction();

            $operation->update(['status' => $newStatus]);
            $this->applyStatusTransition($operation, $newStatus, $oldStatus);

            $this->logOperation($operation, OperationLog::ACTION_STATUS_CHANGED, [
                'status' => ['from' => $oldStatus, 'to' => $newStatus],
            ]);

            DB::commit();

            return redirect()
                ->route('operations.index')
                ->with('success', __('dobs.flash_operation_status_updated'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('operations.index')
                ->with('error', __('dobs.flash_operation_update_error', ['message' => $e->getMessage()]));
        }
    }

    public function export(Operation $operation): StreamedResponse
    {
        $operation->load([
            'item',
            'printingSupplier',
            'ctpSupplier',
            'material',
            'service1',
            'service2',
            'service3',
        ]);

        $filename = 'operation-' . preg_replace('/[^\w\-]+/', '_', $operation->operation_number) . '.csv';

        $rows = [
            [__('dobs.operation_serial'), $operation->operation_number],
            [__('dobs.operation_status'), __('dobs.status_'.strtolower($operation->status))],
            [__('dobs.operation_date'), $operation->operation_date?->format('Y-m-d') ?? ''],
            [__('dobs.operation_current_time'), $operation->formattedOperationTime() ?? ''],
            [__('dobs.operation_product_1'), $operation->item?->name ?? ''],
            [__('dobs.col_quantity'), $operation->quantity ?? ''],
            [__('dobs.operation_statement'), $operation->statement ?? $operation->notes ?? ''],
            [__('dobs.operation_printing_press'), $operation->printingSupplier?->name ?? ''],
            [__('dobs.operation_ctp'), $operation->ctpSupplier?->name ?? ''],
            [__('dobs.operation_color_count'), $operation->color_count ?? ''],
            [__('dobs.operation_paper_material'), $operation->material?->name ?? ''],
            [__('dobs.operation_job_size'), $operation->job_size ?? ''],
            [__('dobs.operation_pull_count'), $operation->pull_count ?? ''],
            [__('dobs.operation_quantity_per_sheet'), $operation->quantity_per_sheet ?? ''],
            [__('dobs.operation_service_1'), $operation->service1?->name ?? ''],
            [__('dobs.operation_service_2'), $operation->service2?->name ?? ''],
            [__('dobs.operation_service_3'), $operation->service3?->name ?? ''],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy(Operation $operation)
    {
        $this->authorizeDelete();

        try {
            DB::beginTransaction();

            $this->logOperation($operation, OperationLog::ACTION_DELETED);

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

    private function applyStatusTransition(Operation $operation, string $newStatus, string $oldStatus): void
    {
        if ($oldStatus !== 'Completed' && $newStatus === 'Completed') {
            $this->deductItemStock((int) $operation->item_id, (int) $operation->quantity);
        } elseif ($oldStatus === 'Completed' && $newStatus !== 'Completed') {
            $this->restoreItemStock((int) $operation->item_id, (int) $operation->quantity);
        }
    }

    private function deductItemStock(int $itemId, int $quantity): void
    {
        $item = Item::find($itemId);

        if ($item) {
            $item->decrement('stock', $quantity);
        }
    }

    private function restoreItemStock(int $itemId, int $quantity): void
    {
        $item = Item::find($itemId);

        if ($item) {
            $item->increment('stock', $quantity);
        }
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function logOperation(Operation $operation, string $action, array $changes = []): void
    {
        OperationLog::create([
            'operation_id' => $operation->id,
            'operation_number' => $operation->operation_number,
            'user_id' => auth()->id(),
            'action' => $action,
            'changes' => $changes !== [] ? $changes : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function diffChanges(array $before, array $after): array
    {
        $changes = [];

        foreach (self::TRACKABLE_FIELDS as $field) {
            $from = $before[$field] ?? null;
            $to = $after[$field] ?? null;

            if ($this->valuesEqual($from, $to)) {
                continue;
            }

            $changes[$field] = ['from' => $from, 'to' => $to];
        }

        return $changes;
    }

    private function valuesEqual(mixed $from, mixed $to): bool
    {
        if ($from === $to) {
            return true;
        }

        if (is_numeric($from) && is_numeric($to)) {
            return (float) $from === (float) $to;
        }

        return (string) $from === (string) $to;
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
