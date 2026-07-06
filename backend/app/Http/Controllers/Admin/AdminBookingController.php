<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreBookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Models\Booking;
use App\Models\Mechanic;
use App\Models\Service;
use App\Support\WorkshopCalendar;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function create(Request $request): View
    {
        $this->ensureAdmin($request);

        $nonWashingServices = Service::query()
            ->active()
            ->selectable()
            ->where('main_category', '!=', 'washing')
            ->get();

        $washingPackages = Service::query()
            ->active()
            ->where('main_category', 'washing')
            ->whereNotNull('sub_category')
            ->where('selection_mode', 0)
            ->get();

        $washingItems = Service::query()
            ->active()
            ->where('main_category', 'washing')
            ->where('selection_mode', 1)
            ->get();

        $washingPackages = $washingPackages->map(function (Service $package) use ($washingItems): Service {
            $items = $washingItems->where('sub_category', $package->sub_category);
            $package->price = (float) $items->sum('price');
            $package->inclusions = $items->pluck('name')->implode(', ');

            return $package;
        });

        $services = $nonWashingServices->concat($washingPackages)
            ->sortBy([
                ['main_category', 'asc'],
                ['sub_category', 'asc'],
                ['name', 'asc'],
            ]);

        $defaultDate = now()->startOfDay();
        $timeSlots = WorkshopCalendar::slotDateTimes($defaultDate)
            ->map(fn ($slot): array => [
                'value' => $slot->format('H:i'),
                'label' => $slot->format('h:i A'),
            ]);
        $mechanics = Mechanic::query()
            ->orderBy('name')
            ->get();

        return view('admin.create-booking', compact('services', 'timeSlots', 'defaultDate', 'mechanics'));
    }

    public function store(AdminStoreBookingRequest $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validated();
        $services = Service::query()
            ->active()
            ->whereIn('id', $validated['service_ids'])
            ->orderBy('name')
            ->get();
        $startsAt = Carbon::parse($validated['booking_date'].' '.$validated['booking_time'])->seconds(0);

        if (! WorkshopCalendar::isSlotAvailable($startsAt, true)) {
            return back()->withInput()->withErrors([
                'booking_time' => 'The selected time slot is already full. Please choose another slot.',
            ]);
        }

        $selectedServices = $services->map(function (Service $service): array {
            $price = (float) $service->price;

            if ($service->main_category === 'washing' && $service->selection_mode === 0) {
                $price = (float) Service::query()
                    ->active()
                    ->where('main_category', 'washing')
                    ->where('sub_category', $service->sub_category)
                    ->where('selection_mode', 1)
                    ->sum('price');
            }

            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $price,
            ];
        })->values()->all();

        $serviceName = $services->pluck('name')->implode(', ');
        $totalAmount = collect($selectedServices)->sum('price');

        Booking::query()->create([
            'user_id' => null,
            'service_id' => $services->first()?->id,
            'service_name' => $serviceName,
            'selected_services' => $selectedServices,
            'total_amount' => $totalAmount,
            'bike_name' => $validated['bike_name'],
            'model' => $validated['model'],
            'plate_number' => $validated['plate_number'],
            'engine_capacity' => $validated['engine_capacity'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?: ('walkin-'.now()->timestamp.'@walkin.local'),
            'customer_phone' => $validated['customer_phone'] ?? null,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?: 'Walk-in customer entry created by admin.',
        ]);

        return redirect()
            ->route('admin.queue')
            ->with('status_success', 'Walk-in booking added to the queue successfully.');
    }

    public function updateStatus(
        UpdateBookingStatusRequest $request,
        Booking $booking
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $nextStatus = $request->validated('status');

        if (! $booking->canTransitionTo($nextStatus)) {
            return back()->withErrors([
                'status' => "Invalid status transition from {$booking->status} to {$nextStatus}.",
            ]);
        }

        $booking->forceFill(['status' => $nextStatus])->save();

        return back()->with('status_success', 'Booking status updated successfully.');
    }

    public function updateMechanic(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'mechanic_id' => ['nullable', 'integer', 'exists:mechanics,id'],
        ]);

        $booking->forceFill([
            'mechanic_id' => $validated['mechanic_id'] ?? null,
        ])->save();

        return back()->with('status_success', 'Assigned mechanic updated successfully.');
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureAdmin($request);

        $booking->delete();

        return back()->with('status_success', 'Booking deleted successfully.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }
}
