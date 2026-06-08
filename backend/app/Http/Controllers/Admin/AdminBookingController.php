<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreBookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Models\Booking;
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

        $services = Service::query()
            ->active()
            ->selectable()
            ->orderBy('main_category')
            ->orderBy('sub_category')
            ->orderBy('name')
            ->get();

        $defaultDate = now()->startOfDay();
        $timeSlots = WorkshopCalendar::slotDateTimes($defaultDate)
            ->map(fn ($slot): array => [
                'value' => $slot->format('H:i'),
                'label' => $slot->format('h:i A'),
            ]);

        return view('admin.create-booking', compact('services', 'timeSlots', 'defaultDate'));
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

        if ($startsAt->lt(now())) {
            return back()->withInput()->withErrors([
                'booking_time' => 'Walk-in entry cannot be scheduled in the past.',
            ]);
        }

        if (! WorkshopCalendar::isSlotAvailable($startsAt)) {
            return back()->withInput()->withErrors([
                'booking_time' => 'The selected time slot is already full. Please choose another slot.',
            ]);
        }

        $selectedServices = $services->map(fn (Service $service): array => [
            'id' => $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
        ])->values()->all();

        $serviceName = $services->pluck('name')->implode(', ');
        $totalAmount = $services->sum(fn (Service $service): float => (float) $service->price);

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
