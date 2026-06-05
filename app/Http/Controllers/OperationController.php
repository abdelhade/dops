<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PaperType;
use App\Models\Operation;
use App\Models\OperationLog;
use App\Models\OperationStatus;
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
        'operation_status_id',
        'operation_number',
        'operation_date',
        'operation_time',
        'item_id',
        'quantity',
        'statement',
        'printing_supplier_id',
        'ctp_supplier_id',
        'color_count',
        'paper_type_id',
        'job_size',
        'pull_count',
        'quantity_per_sheet',
        'service_1_id',
        'service_2_id',
        'service_3_id',
    ];

    public function index(Request $request)
    {
        $query = Operation::with([
            'item', 'operationStatus', 'printingSupplier', 'ctpSupplier',
            'paperType', 'service1', 'service2', 'service3'
        ]);

        if ($request->filled('operation_number')) {
            $query->where('operation_number', 'like', '%' . $request->operation_number . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->date_to);
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('operation_status_id')) {
            $query->where('operation_status_id', $request->operation_status_id);
        }
        if ($request->filled('printing_supplier_id')) {
            $query->where('printing_supplier_id', $request->printing_supplier_id);
        }
        if ($request->filled('ctp_supplier_id')) {
            $query->where('ctp_supplier_id', $request->ctp_supplier_id);
        }
        if ($request->filled('paper_type_id')) {
            $query->where('paper_type_id', $request->paper_type_id);
        }
        if ($request->filled('color_count')) {
            $query->where('color_count', $request->color_count);
        }
        if ($request->filled('service_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('service_1_id', $request->service_id)
                  ->orWhere('service_2_id', $request->service_id)
                  ->orWhere('service_3_id', $request->service_id);
            });
        }
        if ($request->filled('statement')) {
            $query->where(function ($q) use ($request) {
                $q->where('statement', 'like', '%' . $request->statement . '%')
                  ->orWhere('notes', 'like', '%' . $request->statement . '%');
            });
        }

        $operations = $query->latest()->paginate(50)->withQueryString();

        $items = Item::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $paperTypes = PaperType::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $operationStatuses = OperationStatus::orderBy('sort_order')->get();

        return view('operations.index', compact(
            'operations', 'items', 'suppliers', 'paperTypes', 'services', 'operationStatuses'
        ));
    }

    public function create()
    {
        $this->authorizeCreate();

        $opNumber = 'off-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $op = null;
        if (request()->has('copy_from')) {
            $op = Operation::find(request('copy_from'));
            if ($op) {
                $op = $op->replicate();
                $op->operation_number = null;
                $op->operation_date = null;
                $op->operation_time = null;
            }
        }

        return view('operations.create', array_merge(
            $this->formOptions(),
            ['opNumber' => $opNumber, 'op' => $op]
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate($this->validationRules());

        try {
            DB::beginTransaction();

            $operation = Operation::create($this->mapValidatedToAttributes($validated));

            $status = OperationStatus::find($validated['operation_status_id']);
            if ($status && in_array(strtolower($status->name), ['completed', 'مكتمل', 'منتهي'])) {
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
            'paperType',
            'service1',
            'service2',
            'service3',
            'items.category',
            'items.paperSize',
            'logs.user',
            'operationStatus',
        ]);

        return view('operations.show', compact('operation'));
    }

    public function edit(Operation $operation)
    {
        $this->authorizeEdit();

        if ($this->isCompleted($operation)) {
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

        if ($this->isCompleted($operation)) {
            return redirect()->route('operations.index')
                ->with('error', __('dobs.flash_operation_completed_locked'));
        }

        $validated = $request->validate($this->validationRules($operation));

        try {
            DB::beginTransaction();

            $before = $operation->only(self::TRACKABLE_FIELDS);
            $oldStatusId = $operation->operation_status_id;

            $operation->update($this->mapValidatedToAttributes($validated));

            $this->applyStatusTransition($operation, $validated['operation_status_id'], $oldStatusId);

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
            'operation_status_id' => 'required|exists:operation_statuses,id',
        ]);

        $newStatusId = $validated['operation_status_id'];
        $oldStatusId = $operation->operation_status_id;

        if ($oldStatusId == $newStatusId) {
            return redirect()->route('operations.index');
        }

        try {
            DB::beginTransaction();

            $operation->update(['operation_status_id' => $newStatusId]);
            $this->applyStatusTransition($operation, $newStatusId, $oldStatusId);

            $this->logOperation($operation, OperationLog::ACTION_STATUS_CHANGED, [
                'operation_status_id' => ['from' => $oldStatusId, 'to' => $newStatusId],
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
            'paperType',
            'service1',
            'service2',
            'service3',
            'operationStatus',
        ]);

        $filename = 'operation-' . preg_replace('/[^\w\-]+/', '_', $operation->operation_number) . '.csv';

        $rows = [
            [__('dobs.operation_serial'), $operation->operation_number],
            [__('dobs.operation_status'), $operation->operationStatus?->name ?? ''],
            [__('dobs.operation_date'), $operation->operation_date?->format('Y-m-d') ?? ''],
            [__('dobs.operation_current_time'), $operation->formattedOperationTime() ?? ''],
            [__('dobs.operation_product_1'), $operation->item?->name ?? ''],
            [__('dobs.col_quantity'), $operation->quantity ?? ''],
            [__('dobs.operation_statement'), $operation->statement ?? $operation->notes ?? ''],
            [__('dobs.operation_printing_press'), $operation->printingSupplier?->name ?? ''],
            [__('dobs.operation_ctp'), $operation->ctpSupplier?->name ?? ''],
            [__('dobs.operation_color_count'), $operation->color_count ?? ''],
            [__('dobs.operation_paper_material'), $operation->paperType?->name ?? ''],
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
            'paperTypes' => PaperType::orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
            'operationStatuses' => OperationStatus::orderBy('sort_order')->get(),
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
            'paper_type_id' => 'nullable|exists:paper_types,id',
            'job_size' => 'nullable|numeric|min:0',
            'pull_count' => 'nullable|integer|min:0',
            'quantity_per_sheet' => 'nullable|integer|min:0',
            'service_1_id' => 'nullable|exists:services,id',
            'service_2_id' => 'nullable|exists:services,id',
            'service_3_id' => 'nullable|exists:services,id',
            'operation_status_id' => 'required|exists:operation_statuses,id',
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
            'paper_type_id' => $validated['paper_type_id'] ?? null,
            'job_size' => $validated['job_size'] ?? null,
            'pull_count' => $validated['pull_count'] ?? null,
            'quantity_per_sheet' => $this->calcQuantityPerSheet($validated),
            'service_1_id' => $validated['service_1_id'] ?? null,
            'service_2_id' => $validated['service_2_id'] ?? null,
            'service_3_id' => $validated['service_3_id'] ?? null,
            'operation_status_id' => $validated['operation_status_id'],
            'notes' => $validated['statement'] ?? null,
            'total_amount' => 0,
        ];
    }

    private function applyStatusTransition(Operation $operation, ?int $newStatusId, ?int $oldStatusId): void
    {
        $oldIsCompleted = false;
        if ($oldStatusId) {
            $oldStatus = OperationStatus::find($oldStatusId);
            $oldIsCompleted = $oldStatus && in_array(strtolower($oldStatus->name), ['completed', 'مكتمل', 'منتهي']);
        }

        $newIsCompleted = false;
        if ($newStatusId) {
            $newStatus = OperationStatus::find($newStatusId);
            $newIsCompleted = $newStatus && in_array(strtolower($newStatus->name), ['completed', 'مكتمل', 'منتهي']);
        }

        if (!$oldIsCompleted && $newIsCompleted) {
            $this->deductItemStock((int) $operation->item_id, (int) $operation->quantity);
        } elseif ($oldIsCompleted && !$newIsCompleted) {
            $this->restoreItemStock((int) $operation->item_id, (int) $operation->quantity);
        }
    }
    
    private function isCompleted(Operation $operation): bool
    {
        $status = $operation->operationStatus;
        return $status && in_array(strtolower($status->name), ['completed', 'مكتمل', 'منتهي']);
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
