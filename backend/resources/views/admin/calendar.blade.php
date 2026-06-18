@extends('layouts.admin')
@section('title', 'Calendar')
@php
    $page = 'calendar';
    $calendarRouteName = $calendarRouteName ?? 'admin.calendar';
    $statusRouteName = $statusRouteName ?? 'admin.bookings.status';
    $mechanicRouteName = $mechanicRouteName ?? 'admin.bookings.mechanic';
    $deleteRouteName = $deleteRouteName ?? 'admin.bookings.destroy';
    $canAssignMechanic = $canAssignMechanic ?? true;
    $canManageCapacity = $canManageCapacity ?? true;
    $canUpdateStatus = $canUpdateStatus ?? true;
    $canDeleteBookings = $canDeleteBookings ?? true;
    $statusLabels = [
        \App\Models\Booking::STATUS_PENDING => 'Pending',
        \App\Models\Booking::STATUS_CONFIRMED => 'Confirmed',
        \App\Models\Booking::STATUS_REPAIR => 'Repair',
        \App\Models\Booking::STATUS_WAITING_PART => 'Waiting Part',
        \App\Models\Booking::STATUS_READY_PICKUP => 'Ready Pickup',
        \App\Models\Booking::STATUS_COMPLETED => 'Completed',
        \App\Models\Booking::STATUS_CANCELLED => 'Cancelled',
    ];
    $monthMap = $monthDays->keyBy('date');
    $calendarStart = $selectedMonth->copy()->startOfMonth()->startOfWeek();
    $calendarEnd = $selectedMonth->copy()->endOfMonth()->endOfWeek();
    $categoryColors = [
        'washing' => 'border-green-600/30 bg-green-600/10 text-green-600',
        'tuning' => 'border-yellow-600/30 bg-yellow-600/10 text-yellow-600',
        'engine_checkup' => 'border-red-600/30 bg-red-600/10 text-red-600',
        'maintenance' => 'border-blue-600/30 bg-blue-600/10 text-blue-600',
    ];
@endphp

@section('content')
<section class="space-y-lg">
    <div class="border border-outline-variant bg-surface-container-lowest p-lg">
        <div class="flex flex-col gap-md lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Workshop Calendar</p>
                <h1 class="mt-xs font-headline-lg text-headline-lg text-primary tracking-tighter uppercase">Admin Booking Calendar</h1>
                <p class="mt-sm max-w-3xl text-sm text-on-surface-variant">
                    Click any day to open its booking panel. Days with bookings show a centered counter box.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-sm md:grid-cols-4">
                <div class="border border-primary bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Today</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['today'] }}</p>
                </div>
                <div class="border border-outline-variant bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Week</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['week'] }}</p>
                </div>
                <div class="border border-outline-variant bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Month</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['month'] }}</p>
                </div>
                <div class="border border-outline-variant bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Pending</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['pending_jobs'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-gutter xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-lg">
            <div class="border border-outline-variant bg-surface-container-lowest p-md">
                <div class="flex flex-col gap-sm lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="font-label-sm uppercase tracking-widest text-outline">Monthly View</p>
                        <h2 class="mt-xs font-headline-md text-primary uppercase">{{ $selectedMonth->format('F Y') }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-sm">
                        <a href="{{ route($calendarRouteName, ['month' => $selectedMonth->copy()->subMonth()->format('Y-m-01'), 'date' => $selectedDate->toDateString()]) }}"
                           class="border border-outline-variant bg-white px-md py-sm font-label-sm uppercase tracking-widest hover:bg-surface-container-high">Prev</a>
                        <a href="{{ route($calendarRouteName, ['month' => now()->format('Y-m-01'), 'date' => now()->toDateString()]) }}"
                           class="border border-outline-variant bg-white px-md py-sm font-label-sm uppercase tracking-widest hover:bg-surface-container-high">Today</a>
                        <a href="{{ route($calendarRouteName, ['month' => $selectedMonth->copy()->addMonth()->format('Y-m-01'), 'date' => $selectedDate->toDateString()]) }}"
                           class="border border-outline-variant bg-white px-md py-sm font-label-sm uppercase tracking-widest hover:bg-surface-container-high">Next</a>
                    </div>
                </div>

                <div class="mt-md overflow-x-auto">
                    <div class="min-w-[760px]">
                        <div class="grid grid-cols-7 gap-sm">
                            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                                <div class="border border-outline-variant bg-surface-container-low p-sm text-center font-label-sm uppercase tracking-widest text-outline">
                                    {{ $weekday }}
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-sm grid grid-cols-7 gap-sm">
                            @for($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay())
                                @php
                                    $dayData = $monthMap->get($date->toDateString());
                                    $bookingCount = (int) ($dayData['booking_count'] ?? 0);
                                    $isCurrentMonth = $date->month === $selectedMonth->month;
                                    $isSelected = $date->isSameDay($selectedDate);
                                    $capacityRatio = $settings->max_daily_bookings > 0
                                        ? $bookingCount / $settings->max_daily_bookings
                                        : 0;
                                    $indicatorClasses = ($dayData['is_full'] ?? false)
                                        ? 'bg-error text-on-error'
                                        : ($capacityRatio >= 0.7
                                            ? 'bg-orange-500 text-white'
                                            : 'bg-secondary text-on-secondary');
                                @endphp
                                <a href="{{ route($calendarRouteName, ['month' => $selectedMonth->format('Y-m-01'), 'date' => $date->toDateString()]) }}"
                                   class="flex min-h-[132px] flex-col border p-sm transition-colors {{ $isSelected ? 'border-secondary bg-secondary/10' : 'border-outline-variant bg-white hover:bg-surface-container-low' }} {{ !$isCurrentMonth ? 'opacity-40' : '' }}">
                                    <div class="flex items-start justify-between">
                                        <span class="text-sm font-bold text-primary">{{ $date->day }}</span>
                                    </div>

                                    <div class="flex flex-1 items-center justify-center">
                                        @if($bookingCount > 0)
                                            <div class="flex h-16 w-20 flex-col items-center justify-center rounded-md px-xs text-center {{ $indicatorClasses }}">
                                                <span class="text-[9px] font-label-sm uppercase tracking-widest">Booking</span>
                                                <span class="text-lg font-bold leading-none">{{ $bookingCount }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="mt-md flex flex-wrap gap-sm text-[11px] font-label-sm uppercase tracking-widest">
                    <span class="rounded border border-outline-variant bg-white px-sm py-xs text-outline">No box = no bookings</span>
                    <span class="rounded border border-outline-variant bg-secondary px-sm py-xs text-on-secondary">Red = booked day</span>
                    <span class="rounded border border-outline-variant bg-orange-500 px-sm py-xs text-white">Orange = nearly full</span>
                    <span class="rounded border border-outline-variant bg-error px-sm py-xs text-on-error">Dark red = fully booked</span>
                </div>
            </div>

            <div class="border border-outline-variant bg-surface-container-lowest">
                <div class="border-b border-outline-variant p-md">
                    <p class="font-label-sm uppercase tracking-widest text-outline">Booking Panel</p>
                    <h2 class="mt-xs font-headline-md text-primary uppercase">{{ $selectedDate->format('d F Y') }}</h2>
                    <p class="mt-xs text-sm text-on-surface-variant">
                        {{ $dayBookings->count() }} booking(s) scheduled for this date.
                    </p>
                </div>

                <div class="p-md space-y-md">
                    @forelse($dayBookings as $booking)
                        <div class="rounded border border-outline-variant bg-white p-md">
                            <div class="flex flex-col gap-md lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-sm">
                                        <p class="font-label-sm uppercase tracking-widest text-secondary">{{ $booking->starts_at->format('h:i A') }}</p>
                                        @if($booking->getCategoryKey())
                                            @php
                                                $catDetails = $booking->getCategoryDetails();
                                                $colorClass = $categoryColors[$booking->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                            @endphp
                                            <span class="rounded border px-sm py-0.5 text-[9px] font-label-sm uppercase tracking-widest inline-flex items-center gap-xs {{ $colorClass }}">
                                                <span class="material-symbols-outlined text-[10px]">{{ $catDetails['icon'] }}</span>
                                                {{ $catDetails['label'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-xs font-bold uppercase text-primary">{{ $booking->customer_name }}</p>
                                    <p class="text-sm text-on-surface-variant">{{ $booking->bike_name }} {{ $booking->model }}</p>
                                </div>
                                <span class="rounded-md border border-outline-variant px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                    {{ $statusLabels[$booking->status] ?? ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </div>

                            <div class="mt-md grid gap-sm md:grid-cols-2 xl:grid-cols-5">
                                <div class="rounded border border-outline-variant bg-surface-container-low p-sm xl:col-span-1">
                                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Customer</p>
                                    <p class="mt-xs text-sm font-bold text-primary">{{ $booking->customer_name }}</p>
                                    <p class="mt-xs text-xs text-on-surface-variant">Online booking profile</p>
                                </div>
                                <div class="rounded border border-outline-variant bg-surface-container-low p-sm xl:col-span-1">
                                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Motorcycle</p>
                                    <p class="mt-xs text-sm font-bold text-primary">{{ $booking->bike_name }} {{ $booking->model }}</p>
                                    <p class="mt-xs text-xs text-on-surface-variant">Plate: {{ $booking->plate_number ?: 'N/A' }}</p>
                                </div>
                                <div class="rounded border border-outline-variant bg-surface-container-low p-sm xl:col-span-1">
                                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Service Type</p>
                                    <p class="mt-xs text-sm text-primary">{{ $booking->service_name }}</p>
                                    @if($booking->getCategoryKey())
                                        @php
                                            $catDetails = $booking->getCategoryDetails();
                                            $colorClass = $categoryColors[$booking->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                        @endphp
                                        <div class="mt-xs mb-xs">
                                            <span class="rounded border px-sm py-0.5 text-[9px] font-label-sm uppercase tracking-widest inline-flex items-center gap-xs {{ $colorClass }}">
                                                <span class="material-symbols-outlined text-[10px]">{{ $catDetails['icon'] }}</span>
                                                {{ $catDetails['label'] }}
                                            </span>
                                        </div>
                                    @endif
                                    <p class="mt-xs text-xs text-on-surface-variant">Engine: {{ $booking->engine_capacity ?: 'N/A' }} CC</p>
                                </div>
                                <div class="rounded border border-outline-variant bg-surface-container-low p-sm xl:col-span-1">
                                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Time Slot</p>
                                    <p class="mt-xs text-sm font-bold text-primary">{{ $booking->starts_at->format('h:i A') }}</p>
                                    <p class="mt-xs text-xs text-on-surface-variant">{{ $booking->starts_at->format('d M Y') }}</p>
                                </div>
                                <div class="rounded border border-outline-variant bg-surface-container-low p-sm xl:col-span-1">
                                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Current Status</p>
                                    <p class="mt-xs text-sm font-bold text-primary">{{ $statusLabels[$booking->status] ?? ucfirst(str_replace('_', ' ', $booking->status)) }}</p>
                                </div>
                                <div class="rounded border border-outline-variant bg-surface-container-low p-sm xl:col-span-1">
                                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Assigned Mechanic</p>
                                    <p class="mt-xs text-sm font-bold text-primary">{{ $booking->mechanic?->name ?? 'Unassigned' }}</p>
                                    <p class="mt-xs text-xs text-on-surface-variant">{{ $booking->mechanic?->specialization ?: 'Workshop team allocation pending' }}</p>
                                </div>
                            </div>

                            <div class="mt-sm rounded border border-outline-variant bg-surface-container-low p-sm">
                                <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">View Details</p>
                                <p class="mt-xs text-sm text-on-surface-variant">{{ $booking->notes ?: 'No description submitted.' }}</p>
                            </div>

                            <div class="mt-md flex flex-col gap-sm xl:flex-row xl:items-center xl:justify-between">
                                @include('admin.partials.mechanic-assignment', [
                                    'booking' => $booking,
                                    'mechanics' => $mechanics,
                                    'mechanicRouteName' => $mechanicRouteName,
                                    'canAssignMechanic' => $canAssignMechanic,
                                    'mechanicSelectClass' => 'min-w-[16rem] border border-outline-variant bg-white px-sm py-sm font-label-sm text-[10px] uppercase tracking-widest',
                                    'mechanicButtonClass' => 'border border-outline-variant px-md py-sm text-[10px] font-label-sm uppercase tracking-widest text-primary hover:bg-surface-container-high',
                                ])
                                <div class="flex flex-wrap gap-sm">
                                    @if($canUpdateStatus)
                                        @foreach($booking->allowedNextStatuses() as $nextStatus)
                                            <form method="POST" action="{{ route($statusRouteName, $booking) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $nextStatus }}">
                                                <button
                                                    class="border border-outline-variant bg-white px-md py-sm text-[10px] font-label-sm uppercase tracking-widest text-primary hover:bg-surface-container-high">
                                                    {{ $statusLabels[$nextStatus] ?? ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                                </button>
                                            </form>
                                        @endforeach
                                    @endif
                                </div>

                                @if($canDeleteBookings)
                                    <form method="POST" action="{{ route($deleteRouteName, $booking) }}" onsubmit="return confirm('Delete this booking?');">
                                            @csrf
                                        @method('DELETE')
                                        <button class="border border-error bg-error/10 px-md py-sm text-[10px] font-label-sm uppercase tracking-widest text-error hover:bg-error hover:text-on-error">
                                            Delete Booking
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded border border-dashed border-outline-variant bg-white p-lg text-center text-on-surface-variant">
                            No bookings scheduled for this day.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-lg">
            <div class="border border-outline-variant bg-surface-container-lowest p-md">
                <p class="font-label-sm uppercase tracking-widest text-outline">Selected Date</p>
                <h3 class="mt-xs font-headline-md text-primary uppercase">{{ $selectedDate->format('d M Y') }}</h3>
                <div class="mt-md space-y-sm">
                    <div class="flex items-center justify-between rounded border border-outline-variant bg-white px-sm py-sm text-sm">
                        <span class="text-on-surface-variant">Bookings</span>
                        <span class="font-bold text-primary">{{ $dayBookings->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded border border-outline-variant bg-white px-sm py-sm text-sm">
                        <span class="text-on-surface-variant">Completed</span>
                        <span class="font-bold text-primary">{{ $statistics['completed_jobs'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded border border-outline-variant bg-white px-sm py-sm text-sm">
                        <span class="text-on-surface-variant">Active Repairs</span>
                        <span class="font-bold text-primary">{{ $statistics['active_repairs'] }}</span>
                    </div>
                </div>
            </div>

            @if($canManageCapacity)
                <div class="border border-outline-variant bg-surface-container-lowest p-md">
                    <h2 class="font-headline-md text-primary uppercase">Capacity Settings</h2>
                    <p class="mt-xs text-sm text-on-surface-variant">
                        Keep workshop booking limits here.
                    </p>
                    <form method="POST" action="{{ route('admin.calendar.settings') }}" class="mt-md space-y-sm">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Operating Start</label>
                            <input type="time" name="operating_start_time" value="{{ $settings->operating_start_time }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm">
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Operating End</label>
                            <input type="time" name="operating_end_time" value="{{ $settings->operating_end_time }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm">
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Max Daily Bookings</label>
                            <input type="number" min="1" name="max_daily_bookings" value="{{ $settings->max_daily_bookings }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm">
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Max Per Slot</label>
                            <input type="number" min="1" name="max_per_slot" value="{{ $settings->max_per_slot }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm">
                        </div>
                        <button class="w-full bg-primary px-md py-sm font-label-sm uppercase tracking-widest text-on-primary hover:bg-secondary">
                            Save Settings
                        </button>
                    </form>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection
