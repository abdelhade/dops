<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\PaperSize;
use App\Models\Item;
use App\Models\Operation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'categories_count' => Category::count(),
            'suppliers_count' => Supplier::count(),
            'paper_sizes_count' => PaperSize::count(),
            'items_count' => Item::count(),
            'operations_count' => Operation::count(),
            'total_sales' => Operation::sum('total_amount'),
        ];

        $recent_operations = Operation::latest()->take(5)->get();
        $recent_items = Item::with(['category', 'supplier', 'paperSize'])->latest()->take(5)->get();
        
        // Items with low stock (threshold: 50)
        $low_stock_items = Item::where('stock', '<', 50)->take(5)->get();

        return view('dashboard', compact('stats', 'recent_operations', 'recent_items', 'low_stock_items'));
    }
}
