<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\WorkshopCalendar;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function availableSlots(Request $request): JsonResponse
    {
        $date = $request->query('date');

        abort_unless($date, 422, 'A date query parameter is required.');

        $selectedDate = Carbon::parse($date)->startOfDay();
        $settings = WorkshopCalendar::settings();
        $slots = WorkshopCalendar::availableSlotsForDate($selectedDate);

        return response()->json([
            'data' => [
                'date' => $selectedDate->toDateString(),
                'operating_hours' => [
                    'start' => $settings->operating_start_time,
                    'end' => $settings->operating_end_time,
                ],
                'max_daily_bookings' => $settings->max_daily_bookings,
                'max_per_slot' => $settings->max_per_slot,
                'remaining_daily_capacity' => max(
                    $settings->max_daily_bookings - WorkshopCalendar::activeBookingCountForDate($selectedDate),
                    0,
                ),
                'slots' => $slots->map(fn (array $slot): array => [
                    'time' => $slot['time'],
                    'label' => $slot['label'],
                    'remaining_capacity' => $slot['remaining_capacity'],
                    'is_available' => $slot['is_available'],
                ])->values(),
            ],
        ]);
    }

    public function monthAvailability(Request $request): JsonResponse
    {
        $month = $request->query('month', now()->format('Y-m-01'));

        return response()->json([
            'data' => [
                'month' => Carbon::parse($month)->startOfMonth()->format('Y-m'),
                'days' => WorkshopCalendar::monthAvailability($month)->values(),
            ],
        ]);
    }
}
