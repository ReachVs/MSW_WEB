<?php

namespace App\Http\Controllers;

use App\Enums\MechanicStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\InventoryPart;
use App\Models\Mechanic;
use App\Models\StockMovement;
use App\Support\WorkshopCalendar;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MechanicPortalController extends Controller
{
    public function dashboard(): View
    {
        $activeJobs = Booking::query()
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->count();
        $completedToday = Booking::query()
            ->whereDate('ends_at', today())
            ->where('status', Booking::STATUS_COMPLETED)
            ->count();
        $totalBookings = Booking::query()->count();
        $mechanics = $this->mechanicsList();

        $todayBookings = Booking::query()
            ->whereDate('starts_at', today())
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();

        $workshopCards = Booking::query()
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', array_merge(
            compact('activeJobs', 'completedToday', 'totalBookings', 'todayBookings', 'workshopCards', 'mechanics'),
            $this->sharedViewData(),
            [
                'statusRouteName' => 'mechanic.bookings.status',
                'mechanicRouteName' => 'mechanic.bookings.mechanic',
                'queueRouteName' => 'mechanic.queue',
                'canAssignMechanic' => true,
                'canUpdateStatus' => true,
            ],
        ));
    }

    public function queue(): View
    {
        $mechanics = $this->mechanicsList();
        $activeJobs = Booking::query()
            ->where('status', Booking::STATUS_REPAIR)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();
        $pendingJobs = Booking::query()
            ->where('status', Booking::STATUS_PENDING)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();
        $readyPickupJobs = Booking::query()
            ->where('status', Booking::STATUS_READY_PICKUP)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();
        $waitingPartJobs = Booking::query()
            ->where('status', Booking::STATUS_WAITING_PART)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();
        $confirmedJobs = Booking::query()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();
        $archive = Booking::query()
            ->whereIn('status', Booking::HISTORY_STATUSES)
            ->with('mechanic')
            ->latest('updated_at')
            ->take(20)
            ->get();

        return view('admin.queue', array_merge(
            compact('activeJobs', 'pendingJobs', 'readyPickupJobs', 'waitingPartJobs', 'confirmedJobs', 'archive', 'mechanics'),
            $this->sharedViewData(),
            [
                'statusRouteName' => 'mechanic.bookings.status',
                'mechanicRouteName' => 'mechanic.bookings.mechanic',
                'queueRouteName' => 'mechanic.queue',
                'queueSyncRouteName' => 'mechanic.queue.sync',
                'canAssignMechanic' => true,
                'canUpdateStatus' => true,
                'canDeleteArchive' => false,
                'showAddEntry' => false,
            ],
        ));
    }

    public function calendar(Request $request): View
    {
        $selectedMonth = Carbon::parse($request->query('month', now()->format('Y-m-01')))->startOfMonth();
        $selectedDate = Carbon::parse($request->query('date', now()->toDateString()))->startOfDay();
        $selectedView = $request->query('view', 'month');
        $settings = WorkshopCalendar::settings();
        $monthDays = WorkshopCalendar::monthAvailability($selectedMonth, true);
        $mechanics = $this->mechanicsList();

        $dayBookings = Booking::query()
            ->whereDate('starts_at', $selectedDate->toDateString())
            ->with(['mechanic', 'user'])
            ->orderBy('starts_at')
            ->get();

        $weekStart = $selectedDate->copy()->startOfWeek();
        $weekDays = collect(range(0, 6))->map(function (int $offset) use ($weekStart) {
            $date = $weekStart->copy()->addDays($offset);

            return [
                'date' => $date,
                'bookings' => Booking::query()
                    ->whereDate('starts_at', $date->toDateString())
                    ->with(['mechanic', 'user'])
                    ->orderBy('starts_at')
                    ->get(),
            ];
        });

        $statistics = [
            'today' => Booking::query()->whereDate('starts_at', today())->count(),
            'week' => Booking::query()->whereBetween('starts_at', [today()->startOfWeek(), today()->endOfWeek()])->count(),
            'month' => Booking::query()->whereBetween('starts_at', [$selectedMonth->copy()->startOfMonth(), $selectedMonth->copy()->endOfMonth()])->count(),
            'completed_jobs' => Booking::query()->where('status', Booking::STATUS_COMPLETED)->count(),
            'pending_jobs' => Booking::query()->where('status', Booking::STATUS_PENDING)->count(),
            'active_repairs' => Booking::query()->where('status', Booking::STATUS_REPAIR)->count(),
            'ready_pickup' => Booking::query()->where('status', Booking::STATUS_READY_PICKUP)->count(),
        ];

        return view('admin.calendar', array_merge(
            compact(
                'selectedMonth',
                'selectedDate',
                'selectedView',
                'settings',
                'monthDays',
                'weekDays',
                'dayBookings',
                'statistics',
                'mechanics',
            ),
            $this->sharedViewData(),
            [
                'calendarRouteName' => 'mechanic.calendar',
                'statusRouteName' => 'mechanic.bookings.status',
                'mechanicRouteName' => 'mechanic.bookings.mechanic',
                'canAssignMechanic' => true,
                'canManageCapacity' => false,
                'canUpdateStatus' => true,
                'canDeleteBookings' => false,
            ],
        ));
    }

    public function mechanics(): View
    {
        $mechanics = Mechanic::query()
            ->withCount([
                'bookings as active_jobs_count' => fn ($query) => $query->whereIn('status', Booking::ACTIVE_STATUSES),
                'bookings as completed_jobs_count' => fn ($query) => $query->where('status', Booking::STATUS_COMPLETED),
            ])
            ->with([
                'bookings' => fn ($query) => $query
                    ->whereIn('status', Booking::ACTIVE_STATUSES)
                    ->orderBy('starts_at'),
            ])
            ->orderBy('name')
            ->get();

        $mechanicStatuses = collect(MechanicStatus::cases())
            ->map(fn (MechanicStatus $status) => $status->value)
            ->all();

        $statistics = [
            'total' => $mechanics->count(),
            'available' => $mechanics->where('status', MechanicStatus::Available)->count(),
            'busy' => $mechanics->where('status', MechanicStatus::Busy)->count(),
            'off' => $mechanics->where('status', MechanicStatus::Off)->count(),
        ];

        return view('admin.mechanics', array_merge(
            compact('mechanics', 'mechanicStatuses', 'statistics'),
            $this->sharedViewData(),
            [
                'canCreateMechanic' => false,
                'canEditMechanic' => false,
            ],
        ));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', Booking::statuses())],
        ]);

        if (! $booking->canTransitionTo($validated['status'])) {
            return back()->withErrors([
                'status' => "Invalid status transition from {$booking->status} to {$validated['status']}.",
            ]);
        }

        $booking->forceFill([
            'status' => $validated['status'],
        ])->save();

        return back()->with('status_success', 'Booking status updated successfully.');
    }

    public function updateMechanic(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'mechanic_id' => ['nullable', 'integer', 'exists:mechanics,id'],
        ]);

        $booking->forceFill([
            'mechanic_id' => $validated['mechanic_id'] ?? null,
        ])->save();

        return back()->with('status_success', 'Assigned mechanic updated successfully.');
    }

    public function queueSync(): JsonResponse
    {
        return response()->json([
            'signature' => $this->queueSignature(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function sharedViewData(): array
    {
        return [
            'portalRoutePrefix' => 'mechanic',
            'portalTitle' => 'MECHANIC',
            'showInventoryNav' => true,
            'showMechanicsNav' => true,
            'showLogout' => false,
            'compactSidebar' => true,
        ];
    }

    // ─── Inventory (Read-Only) ───────────────────────────────────────────

    public function inventory(Request $request)
    {
        $categories = Category::withCount('parts')->get();

        // Dashboard Mode
        if (! $request->filled('search') && ! $request->filled('category') && ! $request->filled('category_id')) {
            $totalParts = InventoryPart::count();
            $lowStockParts = InventoryPart::whereColumn('stock_qty', '<=', 'minimum_stock')->count();
            $outOfStockParts = InventoryPart::where('stock_qty', 0)->count();
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

            return $this->renderInventoryView($request, 'admin.inventory', [
                'mode' => 'dashboard',
                'kpi' => $kpi,
                'categories' => $categories,
                'criticalParts' => $criticalParts,
            ]);
        }

        // Parts List Mode
        $query = InventoryPart::with('category');
        $selectedCategory = null;

        if ($request->filled('category_id')) {
            $selectedCategory = Category::find($request->input('category_id'));
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        } elseif ($request->filled('category')) {
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

        return $this->renderInventoryView($request, 'admin.inventory', [
            'mode' => 'parts_list',
            'parts' => $parts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    public function inventoryShow(Request $request, InventoryPart $part)
    {
        $part->load(['category', 'movements.creator']);
        $categories = Category::all();

        return $this->renderInventoryView($request, 'admin.inventory', [
            'mode' => 'part_details',
            'part' => $part,
            'categories' => $categories,
        ]);
    }

    public function inventoryLogs(Request $request)
    {
        $logs = StockMovement::with(['part.category', 'creator'])
            ->latest()
            ->paginate(25);

        return $this->renderInventoryView($request, 'admin.inventory-logs', compact('logs'));
    }

    private function renderInventoryView(Request $request, string $view, array $data)
    {
        $data = array_merge($data, $this->sharedViewData(), [
            'canManageInventory' => false,
        ]);

        if ($request->ajax()) {
            return view($view, $data)->fragment('content');
        }

        return view($view, $data);
    }

    private function mechanicsList()
    {
        return Mechanic::query()
            ->orderBy('name')
            ->get();
    }

    private function queueSignature(): string
    {
        $statusCounts = Booking::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $recentBookings = Booking::query()
            ->select(['id', 'status', 'mechanic_id', 'updated_at'])
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'status' => $booking->status,
                'mechanic_id' => $booking->mechanic_id,
                'updated_at' => $booking->updated_at?->toISOString(),
            ])
            ->all();

        return sha1(json_encode([
            'total' => Booking::query()->count(),
            'status_counts' => $statusCounts,
            'recent_bookings' => $recentBookings,
        ]));
    }
}
