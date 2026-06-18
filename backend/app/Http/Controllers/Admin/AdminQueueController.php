<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Mechanic;
use Illuminate\Http\JsonResponse;
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
        $mechanics = Mechanic::query()
            ->orderBy('name')
            ->get();

        return view('admin.queue', compact('activeJobs', 'pendingJobs', 'readyPickupJobs', 'waitingPartJobs', 'confirmedJobs', 'archive', 'mechanics'));
    }

    public function sync(): JsonResponse
    {
        return response()->json([
            'signature' => $this->queueSignature(),
        ]);
    }

    private function queueSignature(): string
    {
        $statusCounts = Booking::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $recentBookings = Booking::query()
            ->select(['id', 'status', 'mechanic_id', 'updated_at'])
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'status' => $booking->status,
                'mechanic_id' => $booking->mechanic_id,
                'updated_at' => $booking->updated_at?->toISOString(),
            ])
            ->all();

        return sha1(json_encode([
            'total' => Booking::query()->count(),
            'status_counts' => $statusCounts,
            'recent_bookings' => $recentBookings,
        ]));
    }
}
