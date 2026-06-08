<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $activeJobs = Booking::whereIn('status', Booking::ACTIVE_STATUSES)->count();
        $completedToday = Booking::whereDate('ends_at', today())
            ->where('status', Booking::STATUS_COMPLETED)
            ->count();
        $totalBookings = Booking::count();

        $todayBookings = Booking::whereDate('starts_at', today())
            ->with('mechanic')
            ->orderBy('starts_at')
            ->get();

        $workshopCards = Booking::whereIn('status', [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_REPAIR,
            Booking::STATUS_WAITING_PART,
            Booking::STATUS_READY_PICKUP,
        ])
            ->with('mechanic')
            ->orderBy('starts_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'activeJobs',
            'completedToday',
            'totalBookings',
            'todayBookings',
            'workshopCards'
        ));
    }
}
