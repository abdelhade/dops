<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OperationStatus;
use Illuminate\Http\Request;

class OperationStatusController extends Controller
{
    public function index()
    {
        $statuses = OperationStatus::orderBy('sort_order')->get();
        return view('operation_statuses.index', compact('statuses'));
    }

    public function create()
    {
        $this->authorizeCreate();
        return view('operation_statuses.create');
    }

    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'sort_order' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        OperationStatus::create($validated);

        return redirect()->route('operation-statuses.index')->with('success', __('dobs.flash_status_created'));
    }

    public function edit(OperationStatus $operationStatus)
    {
        $this->authorizeEdit();
        return view('operation_statuses.edit', compact('operationStatus'));
    }

    public function update(Request $request, OperationStatus $operationStatus)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'sort_order' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $operationStatus->update($validated);

        return redirect()->route('operation-statuses.index')->with('success', __('dobs.flash_status_updated'));
    }

    public function destroy(OperationStatus $operationStatus)
    {
        $this->authorizeDelete();
        $operationStatus->delete();

        return redirect()->route('operation-statuses.index')->with('success', __('dobs.flash_status_deleted'));
    }
}
