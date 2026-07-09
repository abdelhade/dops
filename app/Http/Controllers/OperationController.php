<?php

namespace App\Http\Controllers;

use App\Enums\OperationSilkUnit;
use App\Enums\OperationStencil;
use App\Models\Client;
use App\Models\Item;
use App\Models\PaperType;
use App\Models\Operation;
use App\Models\OperationLog;
use App\Models\OperationKind;
use App\Models\OperationType;
use App\Models\OperationStatus;
use App\Models\Service;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationController extends Controller
{
    /** @var list<string> */
    private const TRACKABLE_FIELDS = [
        'operation_type_id',
        'operation_kind_id',
        'stencil',
        'silk_unit',
        'operation_status_id',
        'client_id',
        'related_sales_order_number',
        'operation_number',
        'operation_date',
        'operation_time',
        'item_id',
        'quantity',
        'statement',
        'printing_supplier_id',
        'printing_in_date',
        'printing_out_date',
        'ctp_supplier_id',
        'color_count',
        'paper_type_id',
        'job_size',
        'pull_count',
        'quantity_per_sheet',
        'service_1_id',
        'service_1_in_date',
        'service_1_out_date',
        'service_2_id',
        'service_2_in_date',
        'service_2_out_date',
        'service_3_id',
        'service_3_in_date',
        'service_3_out_date',
        'service_4_id',
        'service_4_in_date',
        'service_4_out_date',
        'entry_date',
        'exit_date',
    ];

    public function index(Request $request)
    {
        $operationType = $this->resolveOperationType($request);

        $query = Operation::with([
            'client', 'item', 'operationStatus', 'operationType', 'operationKind', 'printingSupplier', 'ctpSupplier',
            'paperType', 'service1', 'service2', 'service3', 'service4',
        ])->where('operation_type_id', $operationType->id);

        $user = auth()->user();
        if ($user && $user->isDataEntry()) {
            $allowedServiceIds = $user->services()->pluck('services.id')->toArray();
            $query->where(function ($q) use ($allowedServiceIds) {
                $q->whereIn('service_1_id', $allowedServiceIds)
                  ->orWhereIn('service_2_id', $allowedServiceIds)
                  ->orWhereIn('service_3_id', $allowedServiceIds)
                  ->orWhereIn('service_4_id', $allowedServiceIds);
            });
        }

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
        if ($operationType->isOffset() && $request->filled('ctp_supplier_id')) {
            $query->where('ctp_supplier_id', $request->ctp_supplier_id);
        }
        if ($request->filled('paper_type_id')) {
            $query->where('paper_type_id', $request->paper_type_id);
        }
        if ($request->filled('color_count')) {
            $query->where('color_count', $request->color_count);
        }
        if ($operationType->isOffset() && $request->filled('service_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('service_1_id', $request->service_id)
                  ->orWhere('service_2_id', $request->service_id)
                  ->orWhere('service_3_id', $request->service_id);
            });
        }
        if ($operationType->isGeneral() && $request->filled('stencil')) {
            $query->where('stencil', $request->stencil);
        }
        if ($operationType->isGeneral() && $request->filled('silk_unit')) {
            $query->where('silk_unit', $request->silk_unit);
        }
        if ($operationType->isGeneral() && $request->filled('operation_kind_id')) {
            $query->where('operation_kind_id', $request->operation_kind_id);
        }
        if ($request->filled('statement')) {
            $query->where(function ($q) use ($request) {
                $q->where('statement', 'like', '%' . $request->statement . '%')
                  ->orWhere('notes', 'like', '%' . $request->statement . '%');
            });
        }

        $operations = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        $items = Item::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $paperTypes = PaperType::orderBy('name')->get();

        if ($user && $user->isDataEntry()) {
            $services = $user->services()->orderBy('name')->get();
        } else {
            $services = Service::orderBy('name')->get();
        }

        $operationStatuses = OperationStatus::orderBy('sort_order')->get();
        $operationTypes = OperationType::orderBy('sort_order')->orderBy('id')->get();

        $operationKinds = OperationKind::orderBy('sort_order')->orderBy('id')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('operations._cards', compact('operations', 'operationStatuses', 'operationType', 'operationKinds'))->render(),
                'has_more' => $operations->hasMorePages(),
                'next_page_url' => $operations->nextPageUrl()
            ]);
        }

        return view('operations.index', compact(
            'operations', 'items', 'suppliers', 'paperTypes', 'services', 'operationStatuses', 'operationType', 'operationTypes', 'operationKinds'
        ));
    }

    public function create(Request $request)
    {
        $this->authorizeCreate();

        $operationType = $this->resolveOperationType($request);

        $op = null;
        if ($request->has('copy_from')) {
            $source = Operation::find($request->input('copy_from'));
            if ($source) {
                $operationType = $source->operationType ?? $operationType;
                $op = $source->replicate();
                $op->operation_number = null;
                $op->operation_date = null;
                $op->operation_time = null;
            }
        }

        $opNumber = Operation::nextOperationNumber($operationType);

        return view('operations.create', array_merge(
            $this->formOptions(),
            [
                'operationType' => $operationType,
                'opNumber' => $opNumber,
                'op' => $op,
            ]
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate($this->validationRules());

        $user = auth()->user();
        if ($user && $user->isDataEntry()) {
            $allowedServiceIds = $user->services()->pluck('services.id')->toArray();
            $assignedServices = array_filter([
                $request->input('service_1_id'),
                $request->input('service_2_id'),
                $request->input('service_3_id'),
                $request->input('service_4_id'),
            ]);
            $intersect = array_intersect($assignedServices, $allowedServiceIds);
            if (empty($intersect)) {
                return back()->withErrors(['service_1_id' => __('dobs.unauthorized_action')])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $operation = Operation::create($this->mapValidatedToAttributes($validated));

            $status = OperationStatus::find($validated['operation_status_id']);
            if ($status && in_array(strtolower($status->name), ['completed', 'مكتمل', 'منتهي'])
                && ! empty($validated['item_id']) && ! empty($validated['quantity'])) {
                $this->deductItemStock((int) $validated['item_id'], (int) $validated['quantity']);
            }

            $this->logOperation($operation, OperationLog::ACTION_CREATED);

            DB::commit();

            $type = OperationType::findOrFail($validated['operation_type_id']);

            return redirect()
                ->route('operations.index', $this->indexRouteParams($type))
                ->with('success', __('dobs.flash_operation_created'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', __('dobs.flash_operation_create_error', ['message' => $e->getMessage()]));
        }
    }

    public function show(Operation $operation)
    {
        $user = auth()->user();
        if ($user && $user->isDataEntry()) {
            $allowedServiceIds = $user->services()->pluck('services.id')->toArray();
            $intersect = array_intersect($operation->assignedServiceIds(), $allowedServiceIds);
            if (empty($intersect)) {
                abort(403, __('dobs.unauthorized_action'));
            }
        }

        $operation->load([
            'client',
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
            'operationType',
            'operationKind',
        ]);

        return view('operations.show', compact('operation'));
    }

    public function edit(Operation $operation)
    {
        $this->authorizeEdit();

        if ($this->isCompleted($operation)) {
            return redirect()
                ->route('operations.index', $this->indexRouteParams($operation->operationType ?? OperationType::resolveFromRequest('offset')))
                ->with('error', __('dobs.flash_operation_completed_locked'));
        }

        $operation->loadMissing('operationType');

        return view('operations.edit', array_merge(
            $this->formOptions(),
            [
                'operation' => $operation,
                'operationType' => $operation->operationType ?? OperationType::resolveFromRequest('offset'),
                'opNumber' => Operation::nextOperationNumber($operation->operationType ?? OperationType::resolveFromRequest('offset')),
            ]
        ));
    }

    public function update(Request $request, Operation $operation)
    {
        $this->authorizeEdit();

        if ($this->isCompleted($operation)) {
            return redirect()
                ->route('operations.index', $this->indexRouteParams($operation->operationType ?? OperationType::resolveFromRequest('offset')))
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

            $type = OperationType::findOrFail($validated['operation_type_id']);

            return redirect()
                ->route('operations.index', $this->indexRouteParams($type))
                ->with('success', __('dobs.flash_operation_updated'));
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('dobs.flash_operation_status_updated'),
                ]);
            }

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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('dobs.flash_operation_status_updated'),
                ]);
            }

            return redirect()
                ->route('operations.index')
                ->with('success', __('dobs.flash_operation_status_updated'));
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('dobs.flash_operation_update_error', ['message' => $e->getMessage()]),
                ], 422);
            }

            return redirect()
                ->route('operations.index')
                ->with('error', __('dobs.flash_operation_update_error', ['message' => $e->getMessage()]));
        }
    }

    public function export(Operation $operation): StreamedResponse
    {
        $operation->load([
            'client',
            'item',
            'operationType',
            'operationKind',
            'printingSupplier',
            'ctpSupplier',
            'paperType',
            'service1',
            'service2',
            'service3',
            'service4',
            'operationStatus',
        ]);

        $filename = 'operation-' . preg_replace('/[^\w\-]+/', '_', $operation->operation_number) . '.csv';

        $rows = [
            [__('dobs.operation_serial'), $operation->operation_number],
            [__('dobs.operation_type'), $operation->operationType?->name ?? ''],
            [__('dobs.operation_status'), $operation->operationStatus?->name ?? ''],
            [__('dobs.operation_date'), $operation->operation_date?->format('Y-m-d') ?? ''],
        ];

        if ($operation->isGeneral()) {
            $rows[] = [__('dobs.operation_general_entry_date'), $operation->entry_date?->format('Y-m-d') ?? ''];
            $rows[] = [__('dobs.operation_general_exit_date'), $operation->exit_date?->format('Y-m-d') ?? ''];
        }

        $rows = array_merge($rows, [
            [__('dobs.operation_current_time'), $operation->formattedOperationTime() ?? ''],
            [__('dobs.operation_client'), $operation->client?->name ?? ''],
            [__('dobs.operation_related_sales_order_number'), $operation->related_sales_order_number ?? ''],
            [
                $operation->isGeneral() ? __('dobs.operation_silk_final_product') : __('dobs.operation_product_1'),
                $operation->item?->name ?? '',
            ],
            [__('dobs.col_quantity'), $operation->quantity ?? ''],
        ]);

        if ($operation->isGeneral()) {
            $rows[] = [__('dobs.operation_kind'), $operation->operationKind?->name ?? ''];
            $rows[] = [__('dobs.operation_silk_unit'), $operation->silk_unit?->label() ?? ''];
        }

        $rows[] = [__('dobs.operation_statement'), $operation->statement ?? $operation->notes ?? ''];
        $rows[] = [
            $operation->isGeneral() ? __('dobs.operation_silk_supplier') : __('dobs.operation_printing_press'),
            $operation->printingSupplier?->name ?? '',
        ];
        $rows[] = [__('dobs.operation_printing_press_in_date'), $operation->printing_in_date?->format('Y-m-d') ?? ''];
        $rows[] = [__('dobs.operation_printing_press_out_date'), $operation->printing_out_date?->format('Y-m-d') ?? ''];

        if ($operation->isOffset()) {
            $rows[] = [__('dobs.operation_ctp'), $operation->ctpSupplier?->name ?? ''];
        }

        $rows[] = [__('dobs.operation_color_count'), $operation->color_count ?? ''];
        $rows[] = [__('dobs.operation_paper_material'), $operation->paperType?->name ?? ''];

        if ($operation->isGeneral()) {
            $rows[] = [__('dobs.operation_silk_print_preparations'), $operation->stencil?->label() ?? ''];
        }

        if ($operation->isOffset()) {
            $rows = array_merge($rows, [
                [__('dobs.operation_job_size'), $operation->job_size ?? ''],
                [__('dobs.operation_pull_count'), $operation->pull_count ?? ''],
                [__('dobs.operation_quantity_per_sheet'), $operation->quantity_per_sheet ?? ''],
            ]);
        }

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

            return redirect()
                ->route('operations.index', $this->indexRouteParams($operation->operationType ?? OperationType::resolveFromRequest('offset')))
                ->with('success', __('dobs.flash_operation_deleted'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('operations.index', $this->indexRouteParams($operation->operationType ?? OperationType::resolveFromRequest('offset')))
                ->with('error', __('dobs.flash_operation_delete_error', ['message' => $e->getMessage()]));
        }
    }

    public function searchClients(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $clients = Client::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('phone', 'like', '%' . $query . '%');
            })
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name']);

        return response()->json(
            $clients->map(fn (Client $client) => [
                'value' => (string) $client->id,
                'text' => $client->name,
            ])->values()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $user = auth()->user();
        $services = ($user && $user->isDataEntry())
            ? $user->services()->orderBy('name')->get()
            : Service::orderBy('name')->get();

        return [
            'clients' => Client::orderBy('name')->get(),
            'items' => Item::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'paperTypes' => PaperType::orderBy('name')->get(),
            'services' => $services,
            'operationStatuses' => OperationStatus::orderBy('sort_order')->get(),
            'operationKinds' => OperationKind::orderBy('sort_order')->orderBy('id')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(?Operation $operation = null): array
    {
        $operationId = $operation?->id;
        $stencilValues = array_map(fn (OperationStencil $stencil) => $stencil->value, OperationStencil::cases());
        $silkUnitValues = array_map(fn (OperationSilkUnit $unit) => $unit->value, OperationSilkUnit::cases());
        $selectedType = fn (): ?OperationType => OperationType::find(request('operation_type_id'));

        return [
            'operation_type_id' => 'required|exists:operation_types,id',
            'operation_kind_id' => [
                Rule::requiredIf(fn () => $selectedType()?->isGeneral() ?? false),
                'nullable',
                'exists:operation_kinds,id',
            ],
            'stencil' => [
                Rule::requiredIf(fn () => $selectedType()?->isGeneral() ?? false),
                'nullable',
                Rule::in($stencilValues),
            ],
            'silk_unit' => [
                Rule::requiredIf(fn () => $selectedType()?->isGeneral() ?? false),
                'nullable',
                Rule::in($silkUnitValues),
            ],
            'operation_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('operations', 'operation_number')->ignore($operationId),
            ],
            'operation_date' => 'required|date',
            'operation_time' => 'required|date_format:H:i',
            'client_id' => 'nullable|exists:clients,id',
            'related_sales_order_number' => 'nullable|string|max:100',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'statement' => 'nullable|string',
            'printing_supplier_id' => 'nullable|exists:suppliers,id',
            'printing_in_date' => 'nullable|date',
            'printing_out_date' => 'nullable|date',
            'ctp_supplier_id' => 'nullable|exists:suppliers,id',
            'color_count' => 'required|integer|min:1|max:10',
            'paper_type_id' => 'nullable|exists:paper_types,id',
            'job_size' => 'nullable|numeric|min:0',
            'pull_count' => 'nullable|integer|min:0',
            'quantity_per_sheet' => 'nullable|integer|min:0',
            'service_1_id' => 'nullable|exists:services,id',
            'service_1_in_date' => 'nullable|date',
            'service_1_out_date' => 'nullable|date',
            'service_2_id' => 'nullable|exists:services,id',
            'service_2_in_date' => 'nullable|date',
            'service_2_out_date' => 'nullable|date',
            'service_3_id' => 'nullable|exists:services,id',
            'service_3_in_date' => 'nullable|date',
            'service_3_out_date' => 'nullable|date',
            'service_4_id' => 'nullable|exists:services,id',
            'service_4_in_date' => 'nullable|date',
            'service_4_out_date' => 'nullable|date',
            'entry_date' => 'nullable|date',
            'exit_date' => 'nullable|date',
            'operation_status_id' => 'required|exists:operation_statuses,id',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapValidatedToAttributes(array $validated): array
    {
        $type = OperationType::findOrFail($validated['operation_type_id']);

        $attributes = [
            'operation_type_id' => $type->id,
            'operation_number' => $validated['operation_number'],
            'operation_date' => $validated['operation_date'],
            'operation_time' => $validated['operation_time'] . ':00',
            'client_id' => $validated['client_id'] ?? null,
            'related_sales_order_number' => $validated['related_sales_order_number'] ?? null,
            'statement' => $validated['statement'] ?? null,
            'operation_status_id' => $validated['operation_status_id'],
            'notes' => $validated['statement'] ?? null,
            'total_amount' => 0,
            'operation_kind_id' => null,
            'item_id' => null,
            'quantity' => null,
            'printing_supplier_id' => null,
            'printing_in_date' => null,
            'printing_out_date' => null,
            'color_count' => null,
            'paper_type_id' => null,
            'stencil' => null,
            'silk_unit' => null,
            'ctp_supplier_id' => null,
            'job_size' => null,
            'pull_count' => null,
            'quantity_per_sheet' => null,
            'service_1_id' => null,
            'service_1_in_date' => null,
            'service_1_out_date' => null,
            'service_2_id' => null,
            'service_2_in_date' => null,
            'service_2_out_date' => null,
            'service_3_id' => null,
            'service_3_in_date' => null,
            'service_3_out_date' => null,
            'service_4_id' => null,
            'service_4_in_date' => null,
            'service_4_out_date' => null,
            'entry_date' => null,
            'exit_date' => null,
        ];

        $attributes['item_id'] = $validated['item_id'];
        $attributes['quantity'] = $validated['quantity'];
        $attributes['printing_supplier_id'] = $validated['printing_supplier_id'] ?? null;
        $attributes['color_count'] = $validated['color_count'];
        $attributes['paper_type_id'] = $validated['paper_type_id'] ?? null;

        $attributes['printing_in_date'] = $validated['printing_in_date'] ?? null;
        $attributes['printing_out_date'] = $validated['printing_out_date'] ?? null;
        $attributes['entry_date'] = $validated['entry_date'] ?? null;
        $attributes['exit_date'] = $validated['exit_date'] ?? null;

        if ($type->isGeneral()) {
            $attributes['operation_kind_id'] = $validated['operation_kind_id'] ?? null;
            $attributes['stencil'] = $validated['stencil'] ?? null;
            $attributes['silk_unit'] = $validated['silk_unit'] ?? null;

            return $attributes;
        }

        $attributes['ctp_supplier_id'] = $validated['ctp_supplier_id'] ?? null;
        $attributes['job_size'] = $validated['job_size'] ?? null;
        $attributes['pull_count'] = $validated['pull_count'] ?? null;
        $attributes['quantity_per_sheet'] = $this->calcQuantityPerSheet($validated);
        $attributes['service_1_id'] = $validated['service_1_id'] ?? null;
        $attributes['service_1_in_date'] = $validated['service_1_in_date'] ?? null;
        $attributes['service_1_out_date'] = $validated['service_1_out_date'] ?? null;
        $attributes['service_2_id'] = $validated['service_2_id'] ?? null;
        $attributes['service_2_in_date'] = $validated['service_2_in_date'] ?? null;
        $attributes['service_2_out_date'] = $validated['service_2_out_date'] ?? null;
        $attributes['service_3_id'] = $validated['service_3_id'] ?? null;
        $attributes['service_3_in_date'] = $validated['service_3_in_date'] ?? null;
        $attributes['service_3_out_date'] = $validated['service_3_out_date'] ?? null;
        $attributes['service_4_id'] = $validated['service_4_id'] ?? null;
        $attributes['service_4_in_date'] = $validated['service_4_in_date'] ?? null;
        $attributes['service_4_out_date'] = $validated['service_4_out_date'] ?? null;

        return $attributes;
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

        if ($operation->item_id && $operation->quantity) {
            if (! $oldIsCompleted && $newIsCompleted) {
                $this->deductItemStock((int) $operation->item_id, (int) $operation->quantity);
            } elseif ($oldIsCompleted && ! $newIsCompleted) {
                $this->restoreItemStock((int) $operation->item_id, (int) $operation->quantity);
            }
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

        if ($from instanceof \BackedEnum) {
            $from = $from->value;
        } elseif ($from instanceof \UnitEnum) {
            $from = $from->name;
        }

        if ($to instanceof \BackedEnum) {
            $to = $to->value;
        } elseif ($to instanceof \UnitEnum) {
            $to = $to->name;
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

    private function resolveOperationType(Request $request): OperationType
    {
        return OperationType::resolveFromRequest(
            $request->input('operation_type'),
            $request->filled('operation_type_id') ? (int) $request->input('operation_type_id') : null
        );
    }

    /**
     * @return array{operation_type: string}
     */
    private function indexRouteParams(OperationType $type): array
    {
        return ['operation_type' => $type->slug];
    }
}
