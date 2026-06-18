<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\WorkshopSetting;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class WorkshopCalendar
{
    public static function settings(): WorkshopSetting
    {
        return WorkshopSetting::current();
    }

    /**
     * @return Collection<int, array{time:string,label:string,starts_at:CarbonImmutable,remaining_capacity:int,is_available:bool}>
     */
    public static function availableSlotsForDate(Carbon|string $date, bool $allowPast = false): Collection
    {
        $settings = self::settings();
        $selectedDate = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();

        if (! $allowPast && $selectedDate->lt($today)) {
            return collect();
        }

        $dailyCount = self::activeBookingCountForDate($selectedDate);
        $dailyRemaining = max($settings->max_daily_bookings - $dailyCount, 0);
        $bookingsBySlot = self::bookingsForDate($selectedDate)
            ->groupBy(fn (Booking $booking): string => $booking->starts_at->format('H:i'));

        return self::slotDateTimes($selectedDate)->map(function (CarbonImmutable $slotStart) use (
            $bookingsBySlot,
            $settings,
            $dailyRemaining,
            $allowPast
        ): array {
            $slotKey = $slotStart->format('H:i');
            $slotCount = $bookingsBySlot->get($slotKey, collect())->count();
            $remainingCapacity = min(
                max($settings->max_per_slot - $slotCount, 0),
                $dailyRemaining,
            );
            $isAvailable = $remainingCapacity > 0 && ($allowPast || $slotStart->greaterThan(now()));

            return [
                'time' => $slotStart->format('H:i'),
                'label' => $slotStart->format('h:i A'),
                'starts_at' => $slotStart,
                'remaining_capacity' => $remainingCapacity,
                'is_available' => $isAvailable,
            ];
        });
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    public static function slotDateTimes(Carbon|string $date): Collection
    {
        $settings = self::settings();
        $selectedDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $cursor = CarbonImmutable::parse(
            $selectedDate->format('Y-m-d').' '.$settings->operating_start_time,
        );
        $end = CarbonImmutable::parse(
            $selectedDate->format('Y-m-d').' '.$settings->operating_end_time,
        );
        $slots = collect();

        while ($cursor->lt($end)) {
            $slots->push($cursor);
            $cursor = $cursor->addHour();
        }

        return $slots;
    }

    public static function isSlotAvailable(Carbon|string $dateTime, bool $allowPast = false): bool
    {
        $slotDateTime = $dateTime instanceof Carbon ? $dateTime->copy() : Carbon::parse($dateTime);
        $slot = self::availableSlotsForDate($slotDateTime->copy()->startOfDay(), $allowPast)
            ->firstWhere('time', $slotDateTime->format('H:i'));

        return (bool) ($slot['is_available'] ?? false);
    }

    public static function activeBookingCountForDate(Carbon|string $date): int
    {
        $selectedDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        return Booking::query()
            ->whereDate('starts_at', $selectedDate->toDateString())
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->count();
    }

    /**
     * @return Collection<int, Booking>
     */
    public static function bookingsForDate(Carbon|string $date): Collection
    {
        $selectedDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        return Booking::query()
            ->whereDate('starts_at', $selectedDate->toDateString())
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * @return Collection<int, array{date:string,booking_count:int,is_full:bool,is_past:bool}>
     */
    public static function monthAvailability(
        Carbon|string $month,
        bool $includeCancelledInCount = false,
    ): Collection {
        $settings = self::settings();
        $baseDate = $month instanceof Carbon ? $month->copy()->startOfMonth() : Carbon::parse($month)->startOfMonth();
        $start = $baseDate->copy()->startOfMonth();
        $end = $baseDate->copy()->endOfMonth();
        $countQuery = Booking::query()
            ->selectRaw('DATE(starts_at) as booking_day, COUNT(*) as booking_count')
            ->whereBetween('starts_at', [$start, $end])
            ->when(
                ! $includeCancelledInCount,
                fn ($query) => $query->whereNotIn('status', [Booking::STATUS_CANCELLED]),
            )
            ->groupBy('booking_day')
            ->pluck('booking_count', 'booking_day');
        $activeCounts = $includeCancelledInCount
            ? Booking::query()
                ->selectRaw('DATE(starts_at) as booking_day, COUNT(*) as booking_count')
                ->whereBetween('starts_at', [$start, $end])
                ->whereNotIn('status', [Booking::STATUS_CANCELLED])
                ->groupBy('booking_day')
                ->pluck('booking_count', 'booking_day')
            : $countQuery;

        return collect(range(1, $baseDate->daysInMonth))->map(function (int $day) use (
            $baseDate,
            $countQuery,
            $activeCounts,
            $settings
        ): array {
            $date = $baseDate->copy()->day($day);
            $count = (int) ($countQuery[$date->toDateString()] ?? 0);
            $activeCount = (int) ($activeCounts[$date->toDateString()] ?? 0);

            return [
                'date' => $date->toDateString(),
                'booking_count' => $count,
                'is_full' => $activeCount >= $settings->max_daily_bookings,
                'is_past' => $date->lt(now()->startOfDay()),
            ];
        });
    }
}
