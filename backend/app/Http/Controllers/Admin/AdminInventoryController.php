<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryPart;
use Illuminate\View\View;

class AdminInventoryController extends Controller
{
    public function index(): View
    {
        $totalParts = InventoryPart::count();
        $lowStockParts = InventoryPart::where('stock_pct', '<', 25)->count();
        $totalValuation = InventoryPart::sum('unit_price_cents'); // Assuming unit_price_cents is stored as cents

        $kpi = [
            'totalParts' => $totalParts,
            'lowStockParts' => $lowStockParts,
            'totalValuation' => $totalValuation,
            'pendingOrders' => 0, // Placeholder, as we don't have an orders table yet
        ];

        $criticalParts = InventoryPart::where('stock_pct', '<', 25)
            ->orderBy('stock_pct')
            ->get();

        $parts = InventoryPart::paginate(10);

        return view('admin.inventory', compact('kpi', 'criticalParts', 'parts'));
    }
}
