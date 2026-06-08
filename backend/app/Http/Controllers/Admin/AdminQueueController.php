<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\View\View;

class AdminQueueController extends Controller
{
    public function index(): View
    {
        $activeJobs = Booking::where('status', Booking::STATUS_REPAIR)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();

        $pendingJobs = Booking::whereIn('status', [
                Booking::STATUS_PENDING,
            ])
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();

        $readyPickupJobs = Booking::where('status', Booking::STATUS_READY_PICKUP)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();

        $waitingPartJobs = Booking::where('status', Booking::STATUS_WAITING_PART)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();

        $confirmedJobs = Booking::where('status', Booking::STATUS_CONFIRMED)
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();

        $archive = Booking::whereIn('status', Booking::HISTORY_STATUSES)
            ->with('mechanic')
            ->latest('updated_at')
            ->take(20)
            ->get();

        return view('admin.queue', compact('activeJobs', 'pendingJobs', 'readyPickupJobs', 'waitingPartJobs', 'confirmedJobs', 'archive'));
    }
}
