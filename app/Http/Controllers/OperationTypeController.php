<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OperationTypeMode;
use App\Models\OperationType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperationTypeController extends Controller
{
    public function index()
    {
        $operationTypes = OperationType::orderBy('sort_order')->orderBy('id')->get();

        return view('operation_types.index', compact('operationTypes'));
    }

    public function create()
    {
        $this->authorizeCreate();

        return view('operation_types.create');
    }

    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate($this->validationRules());

        OperationType::create($validated);

        return redirect()
            ->route('operation-types.index')
            ->with('success', __('dobs.flash_operation_type_created'));
    }

    public function edit(OperationType $operationType)
    {
        $this->authorizeEdit();

        return view('operation_types.edit', compact('operationType'));
    }

    public function update(Request $request, OperationType $operationType)
    {
        $this->authorizeEdit();

        $validated = $request->validate($this->validationRules($operationType));

        if ($operationType->is_system) {
            unset($validated['slug'], $validated['form_mode']);
        }

        $operationType->update($validated);

        return redirect()
            ->route('operation-types.index')
            ->with('success', __('dobs.flash_operation_type_updated'));
    }

    public function destroy(OperationType $operationType)
    {
        $this->authorizeDelete();

        if ($operationType->is_system) {
            return redirect()
                ->route('operation-types.index')
                ->with('error', __('dobs.flash_operation_type_system_locked'));
        }

        return $this->destroyRecord($operationType, 'operation-types.index', 'dobs.flash_operation_type_deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(?OperationType $operationType = null): array
    {
        $modeValues = array_map(fn (OperationTypeMode $mode) => $mode->value, OperationTypeMode::cases());

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('operation_types', 'slug')->ignore($operationType?->id),
            ],
            'form_mode' => ['required', Rule::in($modeValues)],
            'serial_prefix' => 'required|string|max:20',
            'sort_order' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ];
    }
}
