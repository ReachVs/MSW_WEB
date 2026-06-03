<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendBookingConfirmation implements ShouldQueue
{
    public function handle(BookingCreated $event): void
    {
        Notification::route('mail', $event->booking->customer_email)
            ->notify(new BookingConfirmationNotification($event->booking));
    }
}
