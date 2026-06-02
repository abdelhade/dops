<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\PaperSize;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::with(['category', 'supplier', 'paperSize'])->latest()->get();
        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $paperSizes = PaperSize::orderBy('name')->get();
        
        return view('items.create', compact('categories', 'suppliers', 'paperSizes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:items,sku',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'paper_size_id' => 'nullable|exists:paper_sizes,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Auto-generate SKU if empty
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(uniqid());
        }

        Item::create($validated);

        return redirect()->route('items.index')->with('success', __('dobs.flash_item_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->load(['category', 'supplier', 'paperSize', 'operations']);
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $this->authorizeEdit();

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $paperSizes = PaperSize::orderBy('name')->get();

        return view('items.edit', compact('item', 'categories', 'suppliers', 'paperSizes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $this->authorizeEdit();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:items,sku,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'paper_size_id' => 'nullable|exists:paper_sizes,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = $item->sku ?: 'SKU-' . strtoupper(uniqid());
        }

        $item->update($validated);

        return redirect()->route('items.index')->with('success', __('dobs.flash_item_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $this->authorizeDelete();

        $item->delete();
        return redirect()->route('items.index')->with('success', __('dobs.flash_item_deleted'));
    }
}
