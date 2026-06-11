<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OperationKind;
use Illuminate\Http\Request;

class OperationKindController extends Controller
{
    public function index()
    {
        $operationKinds = OperationKind::orderBy('sort_order')->orderBy('id')->get();

        return view('operation_kinds.index', compact('operationKinds'));
    }

    public function create()
    {
        $this->authorizeCreate();

        return view('operation_kinds.create');
    }

    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        OperationKind::create($validated);

        return redirect()
            ->route('operation-kinds.index')
            ->with('success', __('dobs.flash_operation_kind_created'));
    }

    public function edit(OperationKind $operationKind)
    {
        $this->authorizeEdit();

        return view('operation_kinds.edit', compact('operationKind'));
    }

    public function update(Request $request, OperationKind $operationKind)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $operationKind->update($validated);

        return redirect()
            ->route('operation-kinds.index')
            ->with('success', __('dobs.flash_operation_kind_updated'));
    }

    public function destroy(OperationKind $operationKind)
    {
        return $this->destroyRecord($operationKind, 'operation-kinds.index', 'dobs.flash_operation_kind_deleted');
    }
}
