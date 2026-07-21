<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\OperationMovement;
use App\Models\OperationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $query = OperationMovement::with(['operation', 'operationStatus']);

        if ($user && !$user->isAdmin()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            if ($user->isDataEntry() || count($allowedStatusIds) > 0) {
                $query->whereHas('operation', function($q) use ($allowedStatusIds) {
                    $q->whereIn('operation_status_id', $allowedStatusIds);
                });
            }
        }

        $movements = $query->latest('datetime')->paginate(50);

        return view('operation_movements.index', compact('movements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();

        $user = auth()->user();
        $statuses = OperationStatus::orderBy('sort_order')->get();

        $query = Operation::query();
        if ($user && !$user->isAdmin()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            if ($user->isDataEntry() || count($allowedStatusIds) > 0) {
                $query->whereIn('operation_status_id', $allowedStatusIds);
                $statuses = $statuses->whereIn('id', $allowedStatusIds);
            }
        }
        $operations = $query->with(['client', 'item'])->orderBy('id', 'desc')->get();
        
        $operationsData = $operations->map(function ($op) {
            return [
                'id' => $op->id,
                'operation_status_id' => $op->operation_status_id,
                'operation_number' => $op->operation_number,
                'client_name' => $op->client->name ?? '',
                'item_name' => $op->item->name ?? '',
                'quantity' => $op->quantity ?? '',
                'statement' => $op->statement ?? $op->notes ?? '',
                'entries' => $op->movements()->where('type', 'entry')->pluck('operation_status_id')->mapWithKeys(function ($sid) {
                    return [$sid => true];
                })->all(),
            ];
        });

        $types = [
            'entry' => __('dobs.type_entry'),
            'start' => __('dobs.type_start'),
            'end' => __('dobs.type_end'),
            'exit' => __('dobs.type_exit'),
        ];

        return view('operation_movements.create', compact('operations', 'statuses', 'types', 'operationsData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'operation_id' => 'required|exists:operations,id',
            'operation_status_id' => 'nullable|exists:operation_statuses,id',
            'type' => 'required|string|in:entry,start,end,exit',
            'datetime' => 'required|date',
            'next_status_id' => 'nullable|required_if:type,exit|exists:operation_statuses,id',
        ]);

        $user = auth()->user();
        $statusId = $request->input('operation_status_id') ? (int) $request->input('operation_status_id') : null;
        $operationId = (int) $request->input('operation_id');
        $type = $request->input('type');

        // 1. User allowed operation validation
        if ($operationId && $user && !$user->isAdmin()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            if ($user->isDataEntry() || count($allowedStatusIds) > 0) {
                $operationToCheck = Operation::find($operationId);
                if ($operationToCheck && !in_array($operationToCheck->operation_status_id, $allowedStatusIds, true)) {
                    return back()->withErrors(['operation_id' => __('dobs.unauthorized_action')])->withInput();
                }
            }
        }

        // 2. Operation and status connection & state validation
        if ($statusId) {
            $validationErrorResponse = $this->validateMovementLogic($operationId, $statusId, $type, $validated['datetime']);
            if ($validationErrorResponse) {
                return $validationErrorResponse;
            }
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            OperationMovement::create([
                'operation_id' => $validated['operation_id'],
                'operation_status_id' => $validated['operation_status_id'],
                'type' => $validated['type'],
                'datetime' => $validated['datetime'],
            ]);

            if ($type === 'exit' && !empty($validated['next_status_id'])) {
                $nextStatusId = (int) $validated['next_status_id'];
                
                OperationMovement::create([
                    'operation_id' => $operationId,
                    'operation_status_id' => $nextStatusId,
                    'type' => 'entry',
                    'datetime' => date('Y-m-d H:i:s', strtotime($validated['datetime']) + 1),
                ]);

                $operationToUpdate = Operation::find($operationId);
                if ($operationToUpdate) {
                    // Update the operation's status to the new status
                    $operationToUpdate->update(['operation_status_id' => $nextStatusId]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', __('dobs.flash_operation_update_error', ['message' => $e->getMessage()]))->withInput();
        }

        return redirect()
            ->route('operation-movements.index')
            ->with('success', __('dobs.flash_operation_movement_created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OperationMovement $operationMovement)
    {
        $this->authorizeEdit();

        $user = auth()->user();
        $statuses = OperationStatus::orderBy('sort_order')->get();

        $query = Operation::query();
        if ($user && !$user->isAdmin()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            if ($user->isDataEntry() || count($allowedStatusIds) > 0) {
                $query->whereIn('operation_status_id', $allowedStatusIds);
                $statuses = $statuses->whereIn('id', $allowedStatusIds);
            }
        }
        $operations = $query->with(['client', 'item'])->orderBy('id', 'desc')->get();

        $operationsData = $operations->map(function ($op) {
            return [
                'id' => $op->id,
                'operation_status_id' => $op->operation_status_id,
                'operation_number' => $op->operation_number,
                'client_name' => $op->client->name ?? '',
                'item_name' => $op->item->name ?? '',
                'quantity' => $op->quantity ?? '',
                'statement' => $op->statement ?? $op->notes ?? '',
                'entries' => $op->movements()->where('type', 'entry')->pluck('operation_status_id')->mapWithKeys(function ($sid) {
                    return [$sid => true];
                })->all(),
            ];
        });

        $types = [
            'entry' => __('dobs.type_entry'),
            'start' => __('dobs.type_start'),
            'end' => __('dobs.type_end'),
            'exit' => __('dobs.type_exit'),
        ];

        return view('operation_movements.edit', compact('operationMovement', 'operations', 'statuses', 'types', 'operationsData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OperationMovement $operationMovement): RedirectResponse
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'operation_id' => 'required|exists:operations,id',
            'operation_status_id' => 'nullable|exists:operation_statuses,id',
            'type' => 'required|string|in:entry,start,end,exit',
            'datetime' => 'required|date',
        ]);

        $user = auth()->user();
        $statusId = $request->input('operation_status_id') ? (int) $request->input('operation_status_id') : null;
        $operationId = (int) $request->input('operation_id');
        $type = $request->input('type');

        // 1. User allowed operation validation
        if ($operationId && $user && !$user->isAdmin()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            if ($user->isDataEntry() || count($allowedStatusIds) > 0) {
                $operationToCheck = Operation::find($operationId);
                if ($operationToCheck && !in_array($operationToCheck->operation_status_id, $allowedStatusIds, true)) {
                    return back()->withErrors(['operation_id' => __('dobs.unauthorized_action')])->withInput();
                }
            }
        }

        // 2. Operation and status connection & state validation
        if ($statusId) {
            $validationErrorResponse = $this->validateMovementLogic($operationId, $statusId, $type, $validated['datetime'], $operationMovement->id);
            if ($validationErrorResponse) {
                return $validationErrorResponse;
            }
        }

        $operationMovement->update($validated);

        return redirect()
            ->route('operation-movements.index')
            ->with('success', __('dobs.flash_operation_movement_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OperationMovement $operationMovement): RedirectResponse
    {
        return $this->destroyRecord($operationMovement, 'operation-movements.index', 'dobs.flash_operation_movement_deleted');
    }
    /**
     * Validate the chronological and sequence logic of the movement.
     */
    private function validateMovementLogic(int $operationId, int $statusId, string $type, string $datetime, ?int $ignoreMovementId = null): ?RedirectResponse
    {
        $query = OperationMovement::where('operation_id', $operationId);
        
        if ($ignoreMovementId) {
            $query->where('id', '!=', $ignoreMovementId);
        }

        $latestMovement = (clone $query)->orderBy('datetime', 'desc')->first();

        // 1. Chronological Validation
        if ($latestMovement && \Carbon\Carbon::parse($datetime)->lt($latestMovement->datetime)) {
            return back()->withErrors(['datetime' => __('dobs.movement_datetime_must_be_after_latest')])->withInput();
        }

        // 2. Sequence Validation within the specific status
        $latestStatusMovement = (clone $query)->where('operation_status_id', $statusId)
                                              ->orderBy('datetime', 'desc')
                                              ->first();

        $latestType = $latestStatusMovement ? $latestStatusMovement->type : null;

        if ($type === 'entry') {
            if ($latestType && $latestType !== 'exit') {
                 return back()->withErrors(['type' => __('dobs.movement_cannot_entry_already_inside')])->withInput();
            }
        } elseif (in_array($type, ['start', 'end', 'exit'], true)) {
            if (! $latestType) {
                 return back()->withErrors(['operation_id' => __('dobs.operation_must_have_entry_movement')])->withInput();
            }
            
            if ($type === 'start') {
                if ($latestType !== 'entry') {
                    return back()->withErrors(['type' => __('dobs.movement_start_requires_entry')])->withInput();
                }
            } elseif ($type === 'end') {
                if ($latestType !== 'start') {
                    return back()->withErrors(['type' => __('dobs.movement_end_requires_start')])->withInput();
                }
            } elseif ($type === 'exit') {
                if (!in_array($latestType, ['entry', 'end'])) {
                    return back()->withErrors(['type' => __('dobs.movement_exit_requires_end_or_entry')])->withInput();
                }
            }
        }

        return null; // Valid
    }
}
