<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendBookingRemindersCommand extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'Queue reminder notifications for upcoming bookings.';

    public function handle(): int
    {
        $queued = 0;

        Booking::query()
            ->where('status', 'confirmed')
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [now(), now()->addDay()])
            ->orderBy('starts_at')
            ->chunkById(100, function ($bookings) use (&$queued): void {
                foreach ($bookings as $booking) {
                    Notification::route('mail', $booking->customer_email)
                        ->notify(new BookingReminderNotification($booking));

                    $booking->forceFill(['reminder_sent_at' => now()])->save();
                    $queued++;
                }
            });

        $this->info("Queued {$queued} booking reminders.");

        return self::SUCCESS;
    }
}
