<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Support\WorkshopCalendar;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $month = Carbon::parse($request->query('month', now()->format('Y-m-01')))->startOfMonth();
        $monthDays = WorkshopCalendar::monthAvailability($month, true);
        $settings = WorkshopCalendar::settings();

        return response()->json([
            'data' => [
                'month' => $month->format('Y-m'),
                'settings' => [
                    'operating_start_time' => $settings->operating_start_time,
                    'operating_end_time' => $settings->operating_end_time,
                    'max_daily_bookings' => $settings->max_daily_bookings,
                    'max_per_slot' => $settings->max_per_slot,
                ],
                'days' => $monthDays->values(),
                'statistics' => [
                    'today' => Booking::query()->whereDate('starts_at', today())->count(),
                    'week' => Booking::query()->whereBetween('starts_at', [today()->startOfWeek(), today()->endOfWeek()])->count(),
                    'month' => Booking::query()->whereBetween('starts_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count(),
                    'completed_jobs' => Booking::query()->where('status', Booking::STATUS_COMPLETED)->count(),
                    'pending_jobs' => Booking::query()->where('status', Booking::STATUS_PENDING)->count(),
                    'active_repairs' => Booking::query()->where('status', Booking::STATUS_REPAIR)->count(),
                ],
            ],
        ]);
    }

    public function day(Request $request, string $date): JsonResponse
    {
        $this->ensureAdmin($request);

        $selectedDate = Carbon::parse($date)->startOfDay();

        $bookings = Booking::query()
            ->whereDate('starts_at', $selectedDate->toDateString())
            ->with(['mechanic', 'user'])
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'data' => [
                'date' => $selectedDate->toDateString(),
                'bookings' => BookingResource::collection($bookings)->resolve(),
            ],
        ]);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403, 'Admin access is required.');
    }
}
