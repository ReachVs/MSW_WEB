<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryPart;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminInventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $categories = Category::withCount('parts')->get();

        // 1. Dashboard Mode (No search, no category filter)
        if (! $request->filled('search') && ! $request->filled('category') && ! $request->filled('category_id')) {
            $totalParts = InventoryPart::count();
            $lowStockParts = InventoryPart::whereColumn('stock_qty', '<=', 'minimum_stock')->count();
            $outOfStockParts = InventoryPart::where('stock_qty', 0)->count();

            // Valuation = SUM(stock_qty * unit_price_cents)
            $totalValuation = (int) (InventoryPart::query()->selectRaw('SUM(stock_qty * unit_price_cents) as total')->value('total') ?? 0);

            $kpi = [
                'totalParts' => $totalParts,
                'lowStockParts' => $lowStockParts,
                'outOfStockParts' => $outOfStockParts,
                'totalValuation' => $totalValuation,
            ];

            $criticalParts = InventoryPart::with('category')
                ->whereColumn('stock_qty', '<=', 'minimum_stock')
                ->orderBy('stock_qty')
                ->get();

            return $this->renderView($request, 'admin.inventory', [
                'mode' => 'dashboard',
                'kpi' => $kpi,
                'categories' => $categories,
                'criticalParts' => $criticalParts,
            ]);
        }

        // 2. Parts List Mode (filtered by Category or Search)
        $query = InventoryPart::with('category');
        $selectedCategory = null;

        if ($request->filled('category_id')) {
            $selectedCategory = Category::find($request->input('category_id'));
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        } elseif ($request->filled('category')) {
            // Backward compatibility for string-based categories
            $categoryName = $request->input('category');
            $selectedCategory = Category::where('name', $categoryName)->first();
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhereHas('category', function ($catQuery) use ($searchTerm) {
                        $catQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $parts = $query->paginate(15)->withQueryString();

        return $this->renderView($request, 'admin.inventory', [
            'mode' => 'parts_list',
            'parts' => $parts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    public function show(Request $request, InventoryPart $part)
    {
        $this->ensureAdmin($request);

        $part->load(['category', 'movements.creator']);
        $categories = Category::all();

        return $this->renderView($request, 'admin.inventory', [
            'mode' => 'part_details',
            'part' => $part,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['required', 'string', 'max:255', 'unique:inventory_parts,sku'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category' => ['nullable', 'string', 'max:255'], // Backward compatibility
            'stock_qty' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        // Resolve Category
        $categoryId = $validated['category_id'] ?? null;
        if (! $categoryId && ! empty($validated['category'])) {
            $cat = Category::firstOrCreate(['name' => $validated['category']]);
            $categoryId = $cat->id;
        }

        $validated['category_id'] = $categoryId ?? Category::where('name', 'Accessories')->value('id');
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 5;
        $validated['status'] = $validated['status'] ?? 'active';

        $validated['unit_price_cents'] = (int) round($validated['unit_price'] * 100);
        unset($validated['unit_price']);
        unset($validated['category']);

        $part = InventoryPart::query()->create($validated);

        // Initial stock log
        if ($part->stock_qty > 0) {
            StockMovement::create([
                'part_id' => $part->id,
                'movement_type' => 'Stock In',
                'quantity' => $part->stock_qty,
                'previous_stock' => 0,
                'new_stock' => $part->stock_qty,
                'remarks' => 'Initial inventory intake',
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.inventory', ['category_id' => $part->category_id])
            ->with('status_success', 'New inventory part added successfully.');
    }

    public function update(Request $request, InventoryPart $part): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['required', 'string', 'max:255', 'unique:inventory_parts,sku,'.$part->id],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category' => ['nullable', 'string', 'max:255'], // Backward compatibility
            'stock_qty' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        // Resolve Category
        $categoryId = $validated['category_id'] ?? null;
        if (! $categoryId && ! empty($validated['category'])) {
            $cat = Category::firstOrCreate(['name' => $validated['category']]);
            $categoryId = $cat->id;
        }

        $validated['category_id'] = $categoryId ?? $part->category_id;
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 5;
        $validated['status'] = $validated['status'] ?? 'active';

        $validated['unit_price_cents'] = (int) round($validated['unit_price'] * 100);
        unset($validated['unit_price']);
        unset($validated['category']);

        $oldStock = $part->stock_qty;
        $newStock = $validated['stock_qty'];

        $part->update($validated);

        // Adjust stock movement logs if stock changed
        if ($oldStock != $newStock) {
            $diff = $newStock - $oldStock;
            StockMovement::create([
                'part_id' => $part->id,
                'movement_type' => 'Adjustment',
                'quantity' => $diff,
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'remarks' => 'Stock adjusted during profile update',
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.inventory.show', $part->id)
            ->with('status_success', 'Inventory part updated successfully.');
    }

    public function destroy(Request $request, InventoryPart $part): RedirectResponse
    {
        $this->ensureAdmin($request);

        $categoryId = $part->category_id;
        $part->delete();

        return redirect()->route('admin.inventory', ['category_id' => $categoryId])
            ->with('status_success', 'Inventory part deleted successfully.');
    }

    public function stockIn(Request $request, InventoryPart $part): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = $validated['quantity'];
        $prev = $part->stock_qty;
        $new = $prev + $qty;

        $part->forceFill(['stock_qty' => $new])->save();

        StockMovement::create([
            'part_id' => $part->id,
            'movement_type' => 'Stock In',
            'quantity' => $qty,
            'previous_stock' => $prev,
            'new_stock' => $new,
            'remarks' => $validated['remarks'] ?? 'Manual stock addition',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.inventory.show', $part->id)
            ->with('status_success', "Successfully stocked in {$qty} units.");
    }

    public function stockOut(Request $request, InventoryPart $part): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$part->stock_qty],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = $validated['quantity'];
        $prev = $part->stock_qty;
        $new = $prev - $qty;

        $part->forceFill(['stock_qty' => $new])->save();

        StockMovement::create([
            'part_id' => $part->id,
            'movement_type' => 'Stock Out',
            'quantity' => -$qty,
            'previous_stock' => $prev,
            'new_stock' => $new,
            'remarks' => $validated['remarks'] ?? 'Manual stock deduction',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.inventory.show', $part->id)
            ->with('status_success', "Successfully stocked out {$qty} units.");
    }

    public function adjust(Request $request, InventoryPart $part): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'remarks' => ['required', 'string', 'max:255'], // Reason is required
        ]);

        $new = $validated['quantity'];
        $prev = $part->stock_qty;
        $diff = $new - $prev;

        $part->forceFill(['stock_qty' => $new])->save();

        StockMovement::create([
            'part_id' => $part->id,
            'movement_type' => 'Adjustment',
            'quantity' => $diff,
            'previous_stock' => $prev,
            'new_stock' => $new,
            'remarks' => $validated['remarks'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.inventory.show', $part->id)
            ->with('status_success', 'Stock level adjusted successfully.');
    }

    public function logs(Request $request)
    {
        $this->ensureAdmin($request);

        $logs = StockMovement::with(['part.category', 'creator'])
            ->latest()
            ->paginate(25);

        return $this->renderView($request, 'admin.inventory-logs', compact('logs'));
    }

    private function renderView(Request $request, string $view, array $data, string $fragment = 'content')
    {
        $data = array_merge($data, [
            'canManageInventory' => true,
            'portalRoutePrefix' => 'admin',
        ]);

        if ($request->ajax()) {
            return view($view, $data)->fragment($fragment);
        }

        return view($view, $data);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }
}
