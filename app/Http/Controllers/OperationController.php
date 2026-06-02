<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $operations = Operation::withCount('items')->latest()->get();
        return view('operations.index', compact('operations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();

        $items = Item::orderBy('name')->get();
        
        // Auto-generate operation number: OP-YYYYMMDD-RAND
        $opNumber = 'OP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        return view('operations.create', compact('items', 'opNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'operation_number' => 'required|string|max:100|unique:operations,operation_number',
            'operation_date' => 'required|date',
            'status' => 'required|in:Draft,Processing,Completed',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $operation = Operation::create([
                'operation_number' => $validated['operation_number'],
                'operation_date' => $validated['operation_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'total_amount' => 0.00,
            ]);

            $totalAmount = 0;
            
            foreach ($validated['items'] as $itemData) {
                $operation->items()->attach($itemData['item_id'], [
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $itemData['quantity'] * $itemData['unit_price'];

                // Deduct stock if status is Completed
                if ($validated['status'] === 'Completed') {
                    $item = Item::find($itemData['item_id']);
                    if ($item) {
                        $item->decrement('stock', $itemData['quantity']);
                    }
                }
            }

            $operation->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->route('operations.index')->with('success', __('dobs.flash_operation_created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('dobs.flash_operation_create_error', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Operation $operation)
    {
        $operation->load('items.category', 'items.paperSize');
        return view('operations.show', compact('operation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Operation $operation)
    {
        $this->authorizeEdit();

        if ($operation->status === 'Completed') {
            return redirect()->route('operations.index')
                ->with('error', __('dobs.flash_operation_completed_locked'));
        }

        $items = Item::orderBy('name')->get();
        $operation->load('items');

        return view('operations.edit', compact('operation', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Operation $operation)
    {
        $this->authorizeEdit();

        if ($operation->status === 'Completed') {
            return redirect()->route('operations.index')
                ->with('error', __('dobs.flash_operation_completed_locked'));
        }

        $validated = $request->validate([
            'operation_number' => 'required|string|max:100|unique:operations,operation_number,' . $operation->id,
            'operation_date' => 'required|date',
            'status' => 'required|in:Draft,Processing,Completed',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // If updating status to Completed, deduct stocks
            // If it was already completed we blocked editing above, so this transition is from Draft/Processing to Completed.
            if ($validated['status'] === 'Completed') {
                foreach ($validated['items'] as $itemData) {
                    $item = Item::find($itemData['item_id']);
                    if ($item) {
                        $item->decrement('stock', $itemData['quantity']);
                    }
                }
            }

            // Sync items (first detach all, then re-attach)
            $operation->items()->detach();

            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $operation->items()->attach($itemData['item_id'], [
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $itemData['quantity'] * $itemData['unit_price'];
            }

            $operation->update([
                'operation_number' => $validated['operation_number'],
                'operation_date' => $validated['operation_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'total_amount' => $totalAmount,
            ]);

            DB::commit();
            return redirect()->route('operations.index')->with('success', __('dobs.flash_operation_updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('dobs.flash_operation_update_error', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operation $operation)
    {
        $this->authorizeDelete();

        try {
            DB::beginTransaction();
            
            // Detach and delete
            $operation->items()->detach();
            $operation->delete();

            DB::commit();
            return redirect()->route('operations.index')->with('success', __('dobs.flash_operation_deleted'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('operations.index')->with('error', __('dobs.flash_operation_delete_error', ['message' => $e->getMessage()]));
        }
    }
}
