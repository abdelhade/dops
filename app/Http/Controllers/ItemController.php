<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\PaperSize;
use App\Support\SpreadsheetExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemController extends Controller
{
    private function itemHeaders(): array
    {
        return [
            __('dobs.item_name'),
            __('dobs.col_sku'),
            __('dobs.item_description'),
            __('dobs.col_category'),
            __('dobs.col_supplier'),
            __('dobs.col_paper_size'),
            __('dobs.col_price'),
            __('dobs.col_stock'),
        ];
    }
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
        return $this->destroyRecord($item, 'items.index', 'dobs.flash_item_deleted');
    }

    public function export(SpreadsheetExporter $exporter): StreamedResponse
    {
        $items = Item::with(['category', 'supplier', 'paperSize'])->latest()->get();
        $rows = $items->map(fn (Item $item) => [
            $item->name,
            $item->sku ?? '',
            $item->description ?? '',
            $item->category?->name ?? '',
            $item->supplier?->name ?? '',
            $item->paperSize?->name ?? '',
            $item->price,
            $item->stock,
        ])->all();

        return $exporter->downloadXlsx('items', $this->itemHeaders(), $rows);
    }

    public function template(SpreadsheetExporter $exporter): StreamedResponse
    {
        return $exporter->downloadTemplate('items', $this->itemHeaders(), [
            __('dobs.import_sample_item_name'),
            '',
            '',
            '',
            '',
            '',
            '0',
            '0',
        ]);
    }

    public function import(Request $request, SpreadsheetExporter $exporter): RedirectResponse
    {
        $this->authorizeCreate();

        $request->validate([
            'file' => ['required', File::types(['xlsx', 'xls', 'csv'])->max(5120)],
        ]);

        $categories = Category::pluck('id', 'name');
        $suppliers = Supplier::pluck('id', 'name');
        $paperSizes = PaperSize::pluck('id', 'name');
        $imported = 0;

        foreach ($exporter->readDataRows($request->file('file')) as $row) {
            $name = $row[0] ?? '';
            if ($name === '') {
                continue;
            }

            $sku = ($row[1] ?? '') !== '' ? $row[1] : 'SKU-' . strtoupper(uniqid());
            $categoryName = $row[3] ?? '';
            $supplierName = $row[4] ?? '';
            $paperSizeName = $row[5] ?? '';

            Item::create([
                'name' => $name,
                'sku' => $sku,
                'description' => ($row[2] ?? '') !== '' ? $row[2] : null,
                'category_id' => $categoryName !== '' ? $categories[$categoryName] ?? null : null,
                'supplier_id' => $supplierName !== '' ? $suppliers[$supplierName] ?? null : null,
                'paper_size_id' => $paperSizeName !== '' ? $paperSizes[$paperSizeName] ?? null : null,
                'price' => is_numeric($row[6] ?? '') ? (float) $row[6] : 0,
                'stock' => is_numeric($row[7] ?? '') ? (int) $row[7] : 0,
            ]);
            $imported++;
        }

        return redirect()
            ->route('items.index')
            ->with('success', __('dobs.flash_import_success', ['count' => $imported]));
    }
}
