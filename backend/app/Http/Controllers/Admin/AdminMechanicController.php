<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MechanicStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Mechanic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMechanicController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

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

        return view('admin.mechanics', [
            'mechanics' => $mechanics,
            'mechanicStatuses' => $mechanicStatuses,
            'statistics' => $statistics,
            'canCreateMechanic' => true,
            'canEditMechanic' => true,
            'portalRoutePrefix' => 'admin',
            'portalTitle' => 'ADMIN',
            'showInventoryNav' => true,
            'showMechanicsNav' => true,
            'showLogout' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', array_map(
                fn (MechanicStatus $status) => $status->value,
                MechanicStatus::cases(),
            ))],
        ]);

        Mechanic::query()->create($validated);

        return back()->with('status_success', 'New mechanic added successfully.');
    }

    public function update(Request $request, Mechanic $mechanic): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', array_map(
                fn (MechanicStatus $status) => $status->value,
                MechanicStatus::cases(),
            ))],
        ]);

        $mechanic->update($validated);

        return back()->with('status_success', 'Mechanic details updated successfully.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }
}
