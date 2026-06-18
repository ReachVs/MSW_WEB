<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Mechanic;
use App\Models\WorkshopSetting;
use App\Support\WorkshopCalendar;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonth = Carbon::parse($request->query('month', now()->format('Y-m-01')))->startOfMonth();
        $selectedDate = Carbon::parse($request->query('date', now()->toDateString()))->startOfDay();
        $selectedView = $request->query('view', 'month');
        $settings = WorkshopCalendar::settings();
        $monthDays = WorkshopCalendar::monthAvailability($selectedMonth, true);
        $mechanics = Mechanic::query()
            ->orderBy('name')
            ->get();
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

        return view('admin.calendar', compact(
            'selectedMonth',
            'selectedDate',
            'selectedView',
            'settings',
            'monthDays',
            'weekDays',
            'dayBookings',
            'statistics',
            'mechanics',
        ));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operating_start_time' => ['required', 'date_format:H:i'],
            'operating_end_time' => ['required', 'date_format:H:i', 'after:operating_start_time'],
            'max_daily_bookings' => ['required', 'integer', 'min:1'],
            'max_per_slot' => ['required', 'integer', 'min:1'],
        ]);

        WorkshopSetting::current()->update($validated);

        return back()->with('status_success', 'Workshop calendar settings updated.');
    }
}
