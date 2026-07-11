<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\OperationMovement;
use App\Models\Service;
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
        $query = OperationMovement::with(['operation', 'service']);

        if ($user && $user->isDataEntry()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            $query->whereHas('operation', function($q) use ($allowedStatusIds) {
                $q->whereIn('operation_status_id', $allowedStatusIds);
            });
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
        $services = Service::orderBy('name')->get();

        $query = Operation::query();
        if ($user && $user->isDataEntry()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            $query->whereIn('operation_status_id', $allowedStatusIds);
        }
        $operations = $query->orderBy('id', 'desc')->get();
        
        $operationsData = $operations->map(function ($op) {
            return [
                'id' => $op->id,
                'operation_number' => $op->operation_number,
                'services' => $op->assignedServiceIds(),
                'entries' => collect($op->assignedServiceIds())->mapWithKeys(function ($sid) use ($op) {
                    return [$sid => $op->hasEntryMovementForService((int) $sid)];
                })->all(),
            ];
        });

        $types = [
            'entry' => __('dobs.type_entry'),
            'start' => __('dobs.type_start'),
            'end' => __('dobs.type_end'),
            'exit' => __('dobs.type_exit'),
        ];

        return view('operation_movements.create', compact('operations', 'services', 'types', 'operationsData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'operation_id' => 'required|exists:operations,id',
            'service_id' => 'nullable|exists:services,id',
            'type' => 'required|string|in:entry,start,end,exit',
            'datetime' => 'required|date',
        ]);

        $user = auth()->user();
        $serviceId = $request->input('service_id') ? (int) $request->input('service_id') : null;
        $operationId = (int) $request->input('operation_id');
        $type = $request->input('type');

        // 1. User allowed operation validation
        if ($operationId && $user && $user->isDataEntry()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            $operationToCheck = Operation::find($operationId);
            if ($operationToCheck && !in_array($operationToCheck->operation_status_id, $allowedStatusIds, true)) {
                return back()->withErrors(['operation_id' => __('dobs.unauthorized_action')])->withInput();
            }
        }

        // 2. Operation and service connection & state validation
        if ($serviceId) {
            $operation = Operation::find($operationId);
            if ($operation) {

                if (! in_array($serviceId, $operation->assignedServiceIds(), true)) {
                    return back()->withErrors(['operation_id' => __('dobs.operation_does_not_contain_service')])->withInput();
                }

                // If movement type is start, end, or exit, it must have an entry movement
                if (in_array($type, ['start', 'end', 'exit'], true)) {
                    if (! $operation->hasEntryMovementForService($serviceId)) {
                        return back()->withErrors(['operation_id' => __('dobs.operation_must_have_entry_movement')])->withInput();
                    }
                }
            }
        }

        OperationMovement::create($validated);

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
        $services = Service::orderBy('name')->get();

        $query = Operation::query();
        if ($user && $user->isDataEntry()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            $query->whereIn('operation_status_id', $allowedStatusIds);
        }
        $operations = $query->orderBy('id', 'desc')->get();

        $operationsData = $operations->map(function ($op) {
            return [
                'id' => $op->id,
                'operation_number' => $op->operation_number,
                'services' => $op->assignedServiceIds(),
                'entries' => collect($op->assignedServiceIds())->mapWithKeys(function ($sid) use ($op) {
                    return [$sid => $op->hasEntryMovementForService((int) $sid)];
                })->all(),
            ];
        });

        $types = [
            'entry' => __('dobs.type_entry'),
            'start' => __('dobs.type_start'),
            'end' => __('dobs.type_end'),
            'exit' => __('dobs.type_exit'),
        ];

        return view('operation_movements.edit', compact('operationMovement', 'operations', 'services', 'types', 'operationsData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OperationMovement $operationMovement): RedirectResponse
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'operation_id' => 'required|exists:operations,id',
            'service_id' => 'nullable|exists:services,id',
            'type' => 'required|string|in:entry,start,end,exit',
            'datetime' => 'required|date',
        ]);

        $user = auth()->user();
        $serviceId = $request->input('service_id') ? (int) $request->input('service_id') : null;
        $operationId = (int) $request->input('operation_id');
        $type = $request->input('type');

        // 1. User allowed operation validation
        if ($operationId && $user && $user->isDataEntry()) {
            $allowedStatusIds = $user->statuses()->pluck('operation_statuses.id')->toArray();
            $operationToCheck = Operation::find($operationId);
            if ($operationToCheck && !in_array($operationToCheck->operation_status_id, $allowedStatusIds, true)) {
                return back()->withErrors(['operation_id' => __('dobs.unauthorized_action')])->withInput();
            }
        }

        // 2. Operation and service connection & state validation
        if ($serviceId) {
            $operation = Operation::find($operationId);
            if ($operation) {

                if (! in_array($serviceId, $operation->assignedServiceIds(), true)) {
                    return back()->withErrors(['operation_id' => __('dobs.operation_does_not_contain_service')])->withInput();
                }

                // If movement type is start, end, or exit, it must have an entry movement
                if (in_array($type, ['start', 'end', 'exit'], true)) {
                    if (! $operation->hasEntryMovementForService($serviceId)) {
                        return back()->withErrors(['operation_id' => __('dobs.operation_must_have_entry_movement')])->withInput();
                    }
                }
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
}
